<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\EventThemeResource;
use App\Models\EventTheme;

class EventThemeController extends Controller
{
    public function index(){
        return EventThemeResource::collection(EventTheme::all())->all();
    }

    public function getById($id)
    {
        $succes = EventTheme::findOrFail($id);
        return new EventThemeResource($succes);
    }

    public function deleteById($id)
    {
        $thematique = EventTheme::find($id);

        if (!$thematique) {
            return response()->json(['message' => 'Thématique non trouvé.'], 404);
        }

        $thematique->delete();

        return response()->json(['status' => 202, 'message' => 'Thématique supprimée avec succès.']);
    }

    public function store(Request $request)
    {
        try {


            $validatedData = $request->validate([
                'evthm_name' => 'required|string',
                'evthm_color' => 'required|string',
                
            ]);
    
            $them = new EventTheme();
            $them->evthm_name = $validatedData['evthm_name'];
            $them->evthm_color = $validatedData['evthm_color'];
            $them->save();

    
            return response()->json(['status' => 201, 'message' => 'Thématique ajouté avec succès', 'thematique' => $them]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout de la thématique.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function put(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'evthm_id' => 'required|integer',
                'evthm_name' => 'required|string',
                'evthm_color' => 'required|string',
            ]);
            $id = $validatedData['evthm_id'];

            $thematique = EventTheme::find($id);
            $thematique->evthm_name = $validatedData['evthm_name'];
            $thematique->evthm_color = $validatedData['evthm_color'];
            $thematique->save();
    
    
            return response()->json(['status' => 200, 'message' => 'Thématique modifiée avec succès']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la modification de la thématique.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
