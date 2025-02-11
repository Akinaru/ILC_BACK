<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UniversityResource;
use App\Models\University;
use App\Models\PartnerCountry;
use App\Models\Agreement;


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
                'parco_id' => ['required', 'string'],
                'parco_name' => 'string|nullable',
                'parco_code' => 'string|nullable',
            ]);
    
            $parco_id = $validatedData['parco_id'];
    
            // Vérifie si on doit créer un nouveau PartnerCountry
            if ($parco_id === 'addNew') {
    
                // Créer un nouveau PartnerCountry
                $newCountry = new PartnerCountry();
                $newCountry->parco_name = $validatedData['parco_name'];
                $newCountry->parco_code = strtolower($validatedData['parco_code']);
                $newCountry->save();
    
                // Assigner le parco_id généré à l'université
                $parco_id = $newCountry->parco_id;
            }
    
            // Créer la nouvelle université
            $university = new University();
            $university->univ_name = $validatedData['univ_name'];
            $university->univ_city = $validatedData['univ_city'];
            $university->parco_id = $parco_id;
            $university->save();
    
            return response()->json(['status' => 201, 'message' => 'L\'accord a été ajouté avec succès.', 'university' => $university]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout de l\'accord.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    

    public function put(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'univ_id' => 'required|integer',
                'univ_name' => 'required|string',
                'univ_city' => 'required|string',
                'parco_id' => 'required|integer',
            ]);
            $id = $validatedData['univ_id'];

            $university = University::find($id);
            $university->univ_name = $validatedData['univ_name'];
            $university->univ_city = $validatedData['univ_city'];
            $university->parco_id = $validatedData['parco_id'];
            $university->save();
    
    
            return response()->json(['status' => 200, 'message' => 'L\'université a été modifié avec succès', 'university' => $university]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la modification de l\'université.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
        
    public function deleteById($id)
    {
        // Trouver l'université par ID
        $university = University::find($id);
    
        if (!$university) {
            return response()->json(['message' => 'Université non trouvée.'], 404);
        }
    
        Agreement::where('univ_id', $id)->update(['univ_id' => null]);
    
        $university->delete();
    
        return response()->json(['status' => 202, 'message' => 'L\'université a été supprimée avec succès.']);
    }
    
}