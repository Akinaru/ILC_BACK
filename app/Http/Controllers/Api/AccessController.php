<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AccessResource;
use App\Models\Access;
use App\Models\Account;

class AccessController extends Controller
{
    public function index(){
        $accessCollection = AccessResource::collection(Access::all())->all();

        return response()->json([
            'access' => $accessCollection,
            'count' => Access::all()->count(),
        ]);
    }



    public function getFiltered()
    {
        $accessData = Access::all();
        $groupedAccess = $accessData->groupBy('acs_accounttype');
    
        $formattedAccess = $groupedAccess->map(function ($accessGroup) {
            return AccessResource::collection($accessGroup)->toArray(request());
        });
    
        return response()->json([
            'access' => $formattedAccess,
            'count' => $accessData->count(),
        ]);
    }
    
    
    public function getByLogin($login)
    {
        $success = Access::where('acc_id', $login)->first();
    
        if ($success) {
            $accessResource = new AccessResource($success);
            
            return response()->json([
                'access' => $accessResource,
                'count' => 1,
            ]);
        } else {
            return response()->json([
                'count' => 0,
            ]);
        }
    }

    public function getMyAccess(Request $request)
    {
        $user = auth()->user();
    
        if (!$user || !$user->acc_id) {
            return response()->json([
                'status' => 404,
                'message' => 'Compte introuvable (utilisateur non connecté ou identifiant manquant).',
            ]);
        }
    
        $access = Access::where('acc_id', $user->acc_id)->first();
    
        if ($access) {
            return response()->json([
                'access' => new AccessResource($access),
                'count' => 1,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun accès trouvé pour ce compte.',
                'count' => 0,
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'acc_id' => 'required|string',
                'acs_accounttype' => 'required|integer',
            ]);

            
    
            $existingAccess = Access::where('acc_id', $validatedData['acc_id'])->first();
    
            if ($existingAccess) {
                $existingAccess->update(['acs_accounttype' => $validatedData['acs_accounttype']]);
    
                return response()->json(['status'=> 201, 'message' => 'Accès mis à jour avec succès pour '.$validatedData['acc_id'].'.']);
            } else {
                $access = new Access();
                $access->acc_id = $validatedData['acc_id'];
                $access->acs_accounttype = $validatedData['acs_accounttype'];
                $access->save();
    
                return response()->json(['status'=> 201 ,'message' => 'Accès créé avec succès pour '.$validatedData['acc_id'].'.']);
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
            'acc_id_action' => 'required|string',
        ]);

        if($validatedData['acc_id'] == $validatedData['acc_id_action']){
            return response()->json(['message' => 'Vous ne pouvez pas vous enlever votre accès.'], 404);
        }
        $acc_id = $validatedData['acc_id'];

        $access = Access::where('acc_id', $acc_id)->first();
        
        if (!$access) {
            return response()->json(['message' => 'L\'accès n\'a pas été trouvé.'], 404);
        }
        if($access->acs_accounttype == 2){
            $account = Account::find($acc_id);
            if($account && $account->acc_validateacc == false){
                $account->dept_id = null;
                $account->save();
            }
        }
        $access->delete();
    
        return response()->json(['status' => 202, 'message' => 'L\'accès de '.$acc_id.' a été supprimé avec succès.']);
    }
    


    
}

