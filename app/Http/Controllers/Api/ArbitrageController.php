<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ArbitrageResource;
use App\Models\Arbitrage;
use App\Models\Account;
use App\Models\Agreement;
use App\Http\Resources\AgreementResource;

class ArbitrageController extends Controller
{
    public function index(){
        return ArbitrageResource::collection(Arbitrage::all())->all();
    }

    public function indexActuel()
    {
        $arbitrages = Arbitrage::whereHas('account', function ($query) {
            $query->where('acc_ancienetu', false);
        })->get();
    
        return ArbitrageResource::collection($arbitrages)->all();
    }

    public function saveArbitrage(Request $request)
    {
        // Validation des données d'entrée
        $validated = $request->validate([
            '*.acc_id' => 'required|exists:t_e_account_acc,acc_id',
            '*.agree_id' => 'required|exists:t_e_agreement_agree,agree_id',
            '*.arb_pos' => 'required|integer',
        ]);
    
        // Supprimer toutes les entrées existantes d'arbitrage
        Arbitrage::truncate();
    
        // Insérer les nouvelles entrées
        foreach ($validated as $arbitrageData) {
            Arbitrage::create([
                'acc_id' => $arbitrageData['acc_id'],
                'agree_id' => $arbitrageData['agree_id'],
                'arb_pos' => $arbitrageData['arb_pos'],
            ]);
        }
    
        return response()->json(['status' => 200, 'save' => 'Sauvegarde automatique', 'message' => 'Les modifications apportées ont été enregistrées.']);
    }

    public function showByAccId($acc_id)
    {
        $arbitrage = Arbitrage::where('acc_id', $acc_id)->first();

        if ($arbitrage) {
            return new ArbitrageResource($arbitrage);
        }

        return response()->json(['status' => 404, 'message' => 'Arbitrage introuvable']);
    }

    public function showMyArbitrage(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$user->acc_id) {
            return response()->json([
                'status' => 403,
                'message' => 'Utilisateur non authentifié ou identifiant manquant.',
            ]);
        }

        $arbitrage = Arbitrage::where('acc_id', $user->acc_id)->first();

        if ($arbitrage) {
            return new ArbitrageResource($arbitrage);
        }

        return response()->json([
            'status' => 404,
            'message' => 'Arbitrage introuvable.',
        ]);
    }
    
    public function modifArbitrage(Request $request){
        $validated = $request->validate([
            'acc_id' => 'required|string',
            'agree_id' => 'required|integer',
            'arb_pos' => 'required|integer',
        ]);
        
        // Vérifier et supprimer l'arbitrage existant pour acc_id
        Arbitrage::where('acc_id', $validated['acc_id'])->delete();

        // Créer le nouvel arbitrage
        $arbitrage = Arbitrage::create([
            'acc_id' => $validated['acc_id'],
            'agree_id' => $validated['agree_id'],
            'arb_pos' => $validated['arb_pos'],
        ]);
        return response()->json(['status' => 200, 'message' => 'Destination changée pour ' . $validated['acc_id'] . '.']);
    }

    public function archiverArbitrage(Request $request)
    {
        // Récupérer tous les arbitrages
        $arbitrages = Arbitrage::all();
    
        foreach ($arbitrages as $arbitrage) {
            // Récupérer tous les comptes qui ont le même acc_id
            $accounts = Account::where('acc_id', $arbitrage->acc_id)->get();
    
            foreach ($accounts as $account) {
                // Récupérer le département via la relation
                $department = $account->department;
    
                // Récupérer l'accord correspondant
                $agreement = Agreement::where('agree_id', $arbitrage->agree_id)->first();
    
                // Mise à jour du compte
                $account->acc_arbitragefait = true;
                $account->acc_ancienetu = true;
                $account->agree_id = $arbitrage->agree_id;
                $account->acc_json_department = $department ? json_encode($department) : null;
                $account->acc_json_agreement = $agreement ? json_encode(new AgreementResource($agreement)) : null;
    
                $account->save();
            }
    
            // Supprimer l'arbitrage après avoir mis à jour les comptes
            $arbitrage->delete();
        }
    
        return response()->json([
            'message' => 'Arbitrages archivés avec succès',
            'status' => 200
        ]);
    }

    public function validerArbitrage(Request $request)
    {
        // Récupérer tous les arbitrages
        $arbitrages = Arbitrage::all();
        
        foreach ($arbitrages as $arbitrage) {
            // Vérifier si l'acc_id est "abelar" (vérification temporaire)
                // Récupérer tous les comptes qui ont le même acc_id que l'arbitrage
                $accounts = Account::where('acc_id', $arbitrage->acc_id)->get();
                
                foreach ($accounts as $account) {
                    // Mettre à jour le compte
                    $account->acc_arbitragefait = true;
                    $account->save();
                }
                
        }
        
        return response()->json(['message' => 'Arbitrages validé avec succès', 'status' => 200]);
    }

    public function desarchiver(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'acc_id' => 'required|string',
            ]);

            // Trouver le compte
            $account = Account::find($validatedData['acc_id']);

            if (!$account) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Compte non trouvé',
                ]);
            }

            // Mise à jour : retirer l'archivage
            $account->acc_arbitragefait = false;
            $account->acc_ancienetu = false;
            $account->agree_id = null;
            $account->acc_json_agreement = null;
            $account->acc_json_department = null;
            $account->save();

            return response()->json([
                'status' => 200,
                'message' => 'Compte désarchivé avec succès.',
                'account' => $account,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Une erreur s\'est produite lors du désarchivage.',
                'message' => $e->getMessage(),
            ]);
        }
    }

}
