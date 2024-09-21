<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UniversityResource;
use App\Models\University;


class UniversityController extends Controller
{
    public function index(){
        return UniversityResource::collection(University::all())->all();
    }

    public function getById($id)
    {
        $succes = University::findOrFail($id);
        return new UniversityResource($succes);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'univ_name' => 'required|string',
                'univ_city' => 'required|string',
                'parco_id' => 'required|integer',
            ]);
    
            $university = new University();
            $university->univ_name = $validatedData['univ_name'];
            $university->univ_city = $validatedData['univ_city'];
            $university->parco_id = $validatedData['parco_id'];
            $university->save();
    
            return response()->json(['status'=> 201 ,'message' => 'L\'accord a été ajoutée avec succès.', 'university' => $university]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout de l\'accord.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

        
    public function deleteById($id)
    {
        
        $university = Agreement::find($id);
        
        if (!$university) {
            return response()->json(['message' => 'Université non trouvée.'], 404);
        }

        $university->delete();

        return response()->json(['status' => 200, 'message' => 'L\'université a été supprimée avec succès.'], 200);
    }
}