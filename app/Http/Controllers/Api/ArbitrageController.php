<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ArbitrageResource;
use App\Models\Arbitrage;

class ArbitrageController extends Controller
{
    public function index(){
        return ArbitrageResource::collection(Arbitrage::all())->all();
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
    
    
}
