<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\DepartmentAgreementResource;
use App\Http\Resources\AgreementResource;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Models\DepartmentAgreement;
use App\Models\Agreement;

class DepartmentAgreementController extends Controller
{
    public function index(){
        return DepartmentAgreementResource::collection(DepartmentAgreement::all())->all();
    }

    public function getById($id)
    {
        $succes = DepartmentAgreement::findOrFail($id);
        return new DepartmentAgreementResource($succes);
    }

    
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'agree_id' => 'required|integer',
                'dept_id' => 'required|integer',
                'deptagree_valide' => 'required|boolean',
            ]);
    
            $departmentagreement = new DepartmentAgreement();
            $departmentagreement->agree_id = $validatedData['agree_id'];
            $departmentagreement->dept_id = $validatedData['dept_id'];
            $departmentagreement->deptagree_valide = $validatedData['deptagree_valide'];
            $departmentagreement->save();

            $agreement = Agreement::find($validatedData['agree_id']);
            $department = Department::find($validatedData['dept_id']);
    
            return response()->json([
                'status'=> 201,
                'message' => 'Le département a été ajoutée avec succès à l\'accord.',
                'departmentagreement' => $departmentagreement,
                'agreement' => new AgreementResource($agreement),
                'department' => new DepartmentResource($department)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout du département à l\'accord.',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function delete($agree_id, $dept_id)
    {
        
        $departmentagreement = DepartmentAgreement::where('agree_id', $agree_id)
            ->where('dept_id', $dept_id)
            ->first();
        
        if (!$departmentagreement) {
            return response()->json(['message' => 'Le département ou l\'accord n\'a pas été trouvé.'], 404);
        }
        $departmentagreement->delete();
    
        return response()->json(['status' => 200, 'message' => 'Le département a été supprimé avec succès de l\'accord.'], 200);
    }

    public function changeVisibilityDept(Request $request) {

        $validatedData = $request->validate([
            'agree_id' => 'required|integer',
            'dept_id' => 'required|integer',
            'dept_shortname' => 'required|string',
            'deptagree_valide' => 'required|boolean',
        ]);
        
        $agreeId = $validatedData['agree_id'];
        $deptId = $validatedData['dept_id'];
        $deptShortName = $validatedData['dept_shortname'];
        $deptAgreeValide = $validatedData['deptagree_valide'];
        
        $departmentAgreement = DepartmentAgreement::where('agree_id', $agreeId)
            ->where('dept_id', $deptId)
            ->first();
    
        if (!$departmentAgreement) {
            return response()->json(['message' => 'Le département ou l\'accord n\'a pas été trouvé.'], 404);
        }
    
        $departmentAgreement->deptagree_valide = $deptAgreeValide;
        $departmentAgreement->save();
    
        // Déterminer le message en fonction de la valeur de deptagree_valide
        $message = $deptAgreeValide 
            ? "Le département {$deptShortName} est maintenant visible." 
            : "Le département {$deptShortName} est maintenant invisible.";
    
        return response()->json(['status' => 200, 'message' => $message], 200);
    }
    
    
}
