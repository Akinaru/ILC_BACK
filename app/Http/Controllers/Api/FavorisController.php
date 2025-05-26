<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\FavorisResource;
use App\Http\Resources\AgreementResource;
use App\Models\Favoris;

class FavorisController extends Controller
{
    public function index()
    {
        return FavorisResource::collection(Favoris::all());
    }

    public function getById($id)
    {
        $succes = Favoris::findOrFail($id);
        return new FavorisResource($succes);
    }

    public function getByLogin($login)
    {
        $favoris = Favoris::where('acc_id', $login)->get();
        $favorisCollection = FavorisResource::collection($favoris);

        return response()->json([
            'favoris' => $favorisCollection,
            'count' => $favorisCollection->count(),
        ]);
    }

    public function getMyFavoris(Request $request)
    {
        $user = auth()->user();
        
        if (!$user || !$user->acc_id) {
            return response()->json([
                'status' => 403,
                'message' => 'Utilisateur non authentifié ou identifiant manquant.',
            ]);
        }

        $favoris = Favoris::where('acc_id', $user->acc_id)->get();
        $favorisCollection = FavorisResource::collection($favoris);

        return response()->json([
            'favoris' => $favorisCollection,
            'count' => $favorisCollection->count(),
        ]);
    }


    public function store(Request $request)
    {
        try {
            $user = auth()->user();
    
            if (!$user || !$user->acc_id) {
                return response()->json([
                    'status' => 403,
                    'message' => 'Utilisateur non authentifié ou identifiant manquant.',
                ]);
            }
    
            $validatedData = $request->validate([
                'agree_id' => 'required|integer',
            ]);
    
            $favoris = new Favoris();
            $favoris->agree_id = $validatedData['agree_id'];
            $favoris->acc_id = $user->acc_id; // 🔒 sécurisé via token
            $favoris->save();
    
            return response()->json([
                'status'=> 201,
                'message' => 'L\'accord a été ajouté aux favoris.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout de l\'accord aux favoris.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function delete($agree_id)
    {
        $user = auth()->user();
    
        if (!$user || !$user->acc_id) {
            return response()->json([
                'status' => 403,
                'message' => 'Utilisateur non authentifié ou identifiant manquant.',
            ]);
        }
    
        $favoris = Favoris::where('agree_id', $agree_id)
            ->where('acc_id', $user->acc_id)
            ->first();
    
        if (!$favoris) {
            return response()->json([
                'message' => 'Le favoris ou l\'accord n\'a pas été trouvé.',
            ], 404);
        }
    
        $favoris->delete();
    
        return response()->json([
            'status' => 200,
            'message' => 'L\'accord a été supprimé des favoris.',
        ]);
    }
}
