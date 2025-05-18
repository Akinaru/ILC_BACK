<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $login = $request->login;
        
        // Récupérer le compte
        $account = Account::where('acc_id', $login)->first();
        
        if (!$account) {
            return response()->json([
                'status' => 404,
                'message' => 'Compte non trouvé'
            ]);
        }

        $account->tokens()->delete();

        // Créer le token
        $token = $account->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'account' => $account,
            'roleInfo' => $account->getRoleInfo()
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté']);
    }
}