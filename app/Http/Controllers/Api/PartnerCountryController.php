<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PartnerCountryResource;
use App\Models\PartnerCountry;
use App\Models\University;


class PartnerCountryController extends Controller
{
    public function index(){
        $partnerCountries = PartnerCountry::orderBy('parco_name')->get();
        return PartnerCountryResource::collection($partnerCountries);
    }

    public function getById($id)
    {
        $succes = PartnerCountry::findOrFail($id);
        return new PartnerCountryResource($succes);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'parco_code' => 'required|string',
                'parco_name' => 'required|string',
            ]);
    
            $pays = new PartnerCountry();
            $pays->parco_code = $validatedData['parco_code'];
            $pays->parco_name = $validatedData['parco_name'];
            $pays->save();
    
            return response()->json(['status'=> 201 ,'message' => 'Le pays a été ajoutée avec succès.', 'partnercountry' => $pays]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout du pays.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function put(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'parco_id' => 'required|integer',
                'parco_name' => 'required|string',
                'parco_code' => 'required|string',
            ]);
            $id = $validatedData['parco_id'];

            $pays = PartnerCountry::find($id);
            $pays->parco_name = $validatedData['parco_name'];
            $pays->parco_code = $validatedData['parco_code'];
            $pays->save();
    
    
            return response()->json(['status' => 200, 'message' => 'Le pays a été modifié avec succès', 'partnercountry' => $pays]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la modification du pays.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteById($id)
    {
        $pays = PartnerCountry::find($id);

        if (!$pays) {
            return response()->json(['message' => 'Pays non trouvée.'], 404);
        }

        University::where('parco_id', $id)->update(['parco_id' => null]);

        $pays->delete();

        return response()->json(['status' => 202, 'message' => 'Pays supprimé avec succès.']);
    }
}