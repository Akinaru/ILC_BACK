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

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'act_description' => 'required|string',
                'acc_id' => 'required|string',
                'dept_id' => 'integer|nullable',
                'agree_id' => 'integer|nullable',
                'art_id' => 'integer|nullable',
                'evt_id' => 'integer|nullable',
                'access' => 'integer|nullable',
                'admin' => 'integer|nullable',
            ]); 
            
            // On enlève tous les champs vides
            $optionalFields = array_filter($validatedData, function($value, $key) {
                return in_array($key, ['dept_id', 'agree_id', 'art_id', 'evt_id', 'access', 'admin']) && !is_null($value);
            }, ARRAY_FILTER_USE_BOTH);
            
            // On vérifie s'il y en a qu'un seul
            if (count($optionalFields) > 1) {
                return response()->json(['error' => 'Il ne peut y avoir qu\'un seul élément dans les actions.'], 422);
            }
            
            $action = new Action();
            $action->act_description = $validatedData['act_description'];
            $action->act_date = DB::raw('NOW()');
            $action->acc_id = $validatedData['acc_id'];
            
            // Attribuer les champs optionnels et définir le type d'action
            foreach ($optionalFields as $key => $value) {
                $action->$key = $value;
                
                // Définir le type d'action en fonction du champ optionnel non nul
                switch ($key) {
                    case 'dept_id':
                        $action->act_type = 'department';
                        break;
                    case 'agree_id':
                        $action->act_type = 'agreement';
                        break;
                    case 'art_id':
                        $action->act_type = 'article';
                        break;
                    case 'evt_id':
                        $action->act_type = 'event';
                        break;
                    case 'access':
                        $action->act_type = 'access';
                        break;
                    case 'admin':
                        $action->act_type = 'admin';
                        break;
                    default:
                        $action->act_type = $action->act_type; // Garde la valeur actuelle
                        break;
                }
            }
            
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
