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
