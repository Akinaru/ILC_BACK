<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Models\Component;
use App\Models\Account;
use App\Models\DepartmentAgreement;
use App\Exports\DepartmentExport;
use Maatwebsite\Excel\Facades\Excel;


class DepartmentController extends Controller
{

    public function index(){
        $departmentCollection = DepartmentResource::collection(Department::all())->all();

        return response()->json([
            'departments' => $departmentCollection,
            'count' => Department::all()->count(),
        ]);
    }

    public function getById($id)
    {
        $succes = Department::findOrFail($id);
        return new DepartmentResource($succes);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'dept_name' => 'required|string',
                'dept_shortname' => 'required|string',
                'dept_color' => 'required|string',

                'comp_id' => 'required_without:newcompo.comp_name|integer',
                'newcompo.comp_name' => 'required_without:comp_id|string',
                'newcompo.comp_shortname' => 'required_without:comp_id|string',
            ]);
    
            if (isset($validatedData['newcompo'])) {          
                $newCompo = new Component();
                $newCompo->comp_name = $validatedData['newcompo']['comp_name'];
                $newCompo->comp_shortname = $validatedData['newcompo']['comp_shortname'];
                $newCompo->save();
            
                $compoId = $newCompo->comp_id;
            } else {
                $compoId = $validatedData['comp_id'];
            }

            $department = new Department();
            $department->dept_name = $validatedData['dept_name'];
            $department->dept_shortname = $validatedData['dept_shortname'];
            $department->dept_color = $validatedData['dept_color'];
            $department->comp_id = $compoId;
            $department->save();
    
            return response()->json(['status'=> 201 ,'message' => 'Le departement a été ajoutée avec succès', 'department' => $department]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout du département.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function put(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'dept_id' => 'required|integer',
                'dept_name' => 'required|string',
                'dept_shortname' => 'required|string',
                'dept_color' => 'required|string',
            ]);
            $id = $validatedData['dept_id'];

            $department = Department::find($id);
            $department->dept_name = $validatedData['dept_name'];
            $department->dept_shortname = $validatedData['dept_shortname'];
            $department->dept_color = $validatedData['dept_color'];
            $department->save();
    
    
            return response()->json(['status' => 200, 'message' => 'Le département '.$department->dept_name .' a été modifié avec succès', 'department' => $department]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la modification du département.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

        
    public function deleteById($id)
    {
        $department = Department::find($id);
    
        if (!$department) {
            return response()->json(['message' => 'Departement non trouvé.'], 404);
        }
    
        Account::where('dept_id', $id)->update(['dept_id' => null]);
    
        DepartmentAgreement::where('dept_id', $id)->delete();
    
        $department->delete();
    
        return response()->json(['status' => 202, 'message' => 'Departement supprimé avec succès.']);
    }


    public function export() 
    {
        return Excel::download(new DepartmentExport, 'departements.csv');
    }
}