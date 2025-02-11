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


    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'agree_id' => 'required|integer',
                'acc_id' => 'required|string',
            ]);
    
            $favoris = new Favoris();
            $favoris->agree_id = $validatedData['agree_id'];
            $favoris->acc_id = $validatedData['acc_id'];
            $favoris->save();
    
            return response()->json(['status'=> 201 ,'message' => 'L\'accord a été ajouté aux favoris.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout de l\'accord aux favoris.',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function delete($acc_id, $agree_id)
    {
        
        $favoris = Favoris::where('agree_id', $agree_id)
            ->where('acc_id', $acc_id)
            ->first();
        
        if (!$favoris) {
            return response()->json(['message' => 'Le favoris ou l\'accord n\'a pas été trouvé.'], 404);
        }
        $favoris->delete();
    
        return response()->json(['status' => 200, 'message' => 'L\'accord a été supprimé des favoris.'], 200);
    }
}
