<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ComponentResource;
use App\Models\Component;
use App\Models\Department;
use App\Models\Account;
use App\Models\DepartmentAgreement;


class ComponentController extends Controller
{
    public function index(){
        
        $componentCollection = ComponentResource::collection(Component::all())->all();
        
        return response()->json([
            'components' => $componentCollection,
            'count' => Component::all()->count(),
        ]);
    }

    public function getById($id)
    {
        $succes = Component::findOrFail($id);
        return new ComponentResource($succes);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'comp_name' => 'required|string',
                'comp_shortname' => 'required|string',
            ]);
    
            $component = new Component();
            $component->comp_name = $validatedData['comp_name'];
            $component->comp_shortname = $validatedData['comp_shortname'];
            $component->save();
    
            return response()->json(['status'=> 201 ,'message' => 'La composante a été ajoutée avec succès', 'component' => $component]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout de la composante.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function put(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'comp_id' => 'required|integer',
                'comp_name' => 'required|string',
                'comp_shortname' => 'required|string',
            ]);
            $id = $validatedData['comp_id'];

            $component = Component::find($id);
            $component->comp_name = $validatedData['comp_name'];
            $component->comp_shortname = $validatedData['comp_shortname'];
            $component->save();
    
    
            return response()->json(['status' => 201, 'message' => 'La composante '.$component->comp_name .' a été modifiée avec succès']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la modification de la composante.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
        
    public function deleteById($id)
    {
        // Trouver la composante
        $component = Component::find($id);
    
        if (!$component) {
            return response()->json(['message' => 'Composante non trouvée.'], 404);
        }
    
        // Trouver tous les départements liés à la composante
        $departments = Department::where('comp_id', $component->comp_id)->get();
    
        foreach ($departments as $department) {
            // Mettre à jour les comptes en leur enlevant le département
            Account::where('dept_id', $department->id)->update(['dept_id' => null]);
    
            // Supprimer les accords du département
            DepartmentAgreement::where('dept_id', $department->id)->delete();
    
            // Supprimer le département
            $department->delete();
        }
    
        // Supprimer la composante
        $component->delete();
    
        return response()->json(['status' => 200, 'message' => 'Composante et ses départements supprimés avec succès.'], 200);
    }
    
}