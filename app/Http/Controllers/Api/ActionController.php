<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ActionResource;
use App\Models\Action;
use Illuminate\Support\Facades\DB;


class ActionController extends Controller
{
    public function index()
    {
        return Action::orderBy('act_date', 'desc')->get();
    }
    
    public function getById($id)
    {
        $action = Action::findOrFail($id);
        return new ActionResource($action);
    }
    
    public function getByLogin($login)
    {
        $actions = Action::where('acc_id', $login)->orderBy('act_date', 'desc')->get();
        return ActionResource::collection($actions);
    }

    public function paginateActions(Request $request)
    {
        try {
            $perPage = min(max((int) $request->get('per_page', 25), 1), 100);
            $query = Action::query()->orderBy('act_date', 'desc');
    
            // Filtre par types (array de strings)
            if ($request->has('types')) {
                $types = $request->get('types');
                if (is_array($types)) {
                    $query->whereIn('act_type', $types);
                }
            }
    
            // Filtre par recherche
            if ($request->filled('search')) {
                $search = strtolower($request->get('search'));
    
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(acc_id) LIKE ?', ["%$search%"]);
                });
    
                // Si tu as une relation avec un modèle Account :
                $query->orWhereHas('account', function ($q) use ($search) {
                    $q->whereRaw('LOWER(acc_fullname) LIKE ?', ["%$search%"]);
                });
            }
    
            $actions = $query->paginate($perPage);
    
            return response()->json([
                'status' => 200,
                'data' => $actions->items(),
                'pagination' => [
                    'current_page' => $actions->currentPage(),
                    'per_page' => $actions->perPage(),
                    'total' => $actions->total(),
                    'last_page' => $actions->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la récupération des actions.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'act_description' => 'required|string',
                'acc_id' => 'required|string',
                'act_type' => 'required|string'
            ]); 
            
            
            $action = new Action();
            $action->act_description = $validatedData['act_description'];
            $action->act_date = DB::raw('NOW()');
            $action->acc_id = $validatedData['acc_id'];
            $action->act_type = $validatedData['act_type'];
            
            
            $action->save();
    
            return response()->json(['status'=> 201, 'message' => 'Action de '.$validatedData['acc_id'].' ajoutée avec succès.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout de l\'action.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    

    public function delete(){
        try{
            Action::truncate();
            return response()->json(['status'=> 202 ,'message' => 'Les actions ont été supprimés avec succès.']);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppressions des actions.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
