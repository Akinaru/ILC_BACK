<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\IscedResource;
use App\Models\Isced;


class IscedController extends Controller
{
    public function index(){
        $isceds = Isced::orderBy('isc_code', 'desc')->get();
        return IscedResource::collection($isceds);
    }

    public function getById($id)
    {
        $succes = Isced::findOrFail($id);
        return new IscedResource($succes);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'isc_code' => 'required|string',
                'isc_name' => 'required|string',
            ]);
    
            $isced = new Isced();
            $isced->isc_code = $validatedData['isc_code'];
            $isced->isc_name = $validatedData['isc_name'];
            $isced->save();
    
            return response()->json(['status'=> 201 ,'message' => 'L\'isced a été ajoutée avec succès.', 'isced' => $isced]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout de l\'isced.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteById($id)
    {
        $isced = Isced::find($id);

        if (!$isced) {
            return response()->json(['message' => 'Isced non trouvée.'], 404);
        }

        $isced->delete();

        return response()->json(['status' => 200, 'message' => 'Isced supprimé avec succès.'], 200);
    }
}