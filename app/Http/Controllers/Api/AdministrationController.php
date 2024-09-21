<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AdministrationResource;
use App\Models\Administration;

class AdministrationController extends Controller
{
    public function index()
    {
        $admin = Administration::find(1);

        if (!$admin) {
            return response()->json(['message' => 'Administration introuvable.'], 404);
        }
        return response()->json($admin);
    }

    public function changeDateLimite(Request $request)
    {
        $validatedData = $request->validate([
            'adm_datelimite' => 'required|date',
        ]);

        $admin = Administration::find(1);
        if (!$admin) {
            $admin = new Administration();
            $admin->adm_id = 1;
            
        }

        $admin->adm_datelimite = $validatedData['adm_datelimite'];
        $admin->save();
        return response()->json(['status'=> 200 ,'message' => 'Date limite modifiée à '.$admin->adm_datelimite.'.']);
    }
}
