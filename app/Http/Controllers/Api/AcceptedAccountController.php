<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AcceptedAccountResource;
use App\Models\AcceptedAccount;
use App\Models\Account;

class AcceptedAccountController extends Controller
{
    public function index()
    {
        $accountCollection = AcceptedAccountResource::collection(AcceptedAccount::all())->all();

        return response()->json([
            'accounts' => $accountCollection,
            'count' => AcceptedAccount::all()->count(),
        ]);
    }

    public function getAcceptedByLogin($login)
    {
        try {
            $account = AcceptedAccount::where('acc_id', $login)->first();
            if ($account) {
                return response()->json(['account' => $account]);
            } else {
                return response()->json(['status' => 404, 'message' => 'Aucun compte trouvé.']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur s\'est produite.', 'message' => $e->getMessage()], 500);
        }
    }

    public function getMyAccepted(Request $request)
    {
        try {
            $user = auth()->user();
    
            if (!$user || !$user->acc_id) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Compte introuvable (utilisateur non connecté ou identifiant manquant).',
                ]);
            }
    
            $account = AcceptedAccount::where('acc_id', $user->acc_id)->first();
    
            if ($account) {
                return response()->json(['account' => $account]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Aucun compte accepté trouvé.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'acc_id' => 'required|string',
            ]);

            $existingAccepted = AcceptedAccount::where('acc_id', $validatedData['acc_id'])->first();
    
            if ($existingAccepted) {
                return response()->json(['status'=>404, 'message' => 'Le compte '.$validatedData['acc_id'] .' est déjà autorisé.']);
            } else {
                $acceptedaccount = new AcceptedAccount();
                $acceptedaccount->acc_id = $validatedData['acc_id'];
                $acceptedaccount->save();
    
                return response()->json(['status'=> 201 ,'message' => 'Autorisation ajoutée pour '.$validatedData['acc_id'].'.']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout de l\'accès.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeImport(Request $request)
    {
        try {
            $requestData = $request->all();
            $addedAccounts = [];
            $existingAccounts = [];
            $invalidAccounts = [];
    
            foreach ($requestData as $data) {
                if (!isset($data['acc_id']) || empty($data['acc_id'])) {
                    $invalidAccounts[] = $data;
                    continue;
                }
    
                $existingAccepted = AcceptedAccount::where('acc_id', $data['acc_id'])->first();
                if ($existingAccepted) {
                    $existingAccounts[] = $data['acc_id'];
                } else {
                    $acceptedaccount = new AcceptedAccount();
                    $acceptedaccount->acc_id = $data['acc_id'];
                    $acceptedaccount->save();
                    $addedAccounts[] = $data['acc_id'];
                }
            }
    
            return response()->json([
                'status' => 201,
                'message' => 'Importation des étudiants terminée.',
                'added_accounts' => $addedAccounts,
                'existing_accounts' => $existingAccounts,
                'invalid_accounts' => $invalidAccounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'importation.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    


    public function delete(Request $request)
    {
        
        $validatedData = $request->validate([
            'acc_id' => 'required|string',
        ]);

        $acc_id = $validatedData['acc_id'];

        $accepted = AcceptedAccount::where('acc_id', $acc_id)->first();
        
        if (!$accepted) {
            return response()->json(['message' => 'L\'utilisateur n\'a pas d\'autorisation.'], 404);
        }
        $accepted->delete();
    
        return response()->json(['status' => 202, 'message' => 'L\'autorisation de '.$acc_id.' a été supprimée avec succès.']);
    }
}
