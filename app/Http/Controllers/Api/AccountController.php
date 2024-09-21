<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\Access;
use Illuminate\Support\Facades\DB;
use App\Exports\AccountExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    public function index(){
        $accountCollection = AccountResource::collection(Account::all())->all();

        return response()->json([
            'accounts' => $accountCollection,
            'count' => Account::all()->count(),
        ]);
    }

    public function students()
    {
        $accountsWithoutAccess = Account::doesntHave('access')
            ->orderBy('acc_fullname')
            ->get();
    
        $accountCollection = AccountResource::collection($accountsWithoutAccess)->all();
    
        return response()->json([
            'accounts' => $accountCollection,
            'count' => $accountsWithoutAccess->count(),
        ]);
    }

    public function getByLogin($login)
    {
        $succes = Account::findOrFail($login);
        return new AccountResource($succes);
    }

    public function getByDept($dept_id)
    {
        $accounts = Account::where('dept_id', $dept_id)
                           ->doesntHave('access')
                           ->get();
    
        $accountCollection = AccountResource::collection($accounts)->all();
    
        return response()->json([
            'accounts' => $accountCollection,
            'count' => $accounts->count(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'acc_id' => 'required|string',
                'acc_fullname' => 'required|string',
            ]);
    
            // Créer un nouveau compte
            $account = new Account();
            $account->acc_id = $validatedData['acc_id'];
            $account->acc_fullname = $validatedData['acc_fullname'];
            $account->save();
    
            // Vérifier s'il existe un enregistrement Access associé à ce compte
            $access = Access::where('acc_id', $account->acc_id)->first();
            if($access){
                $account->acc_validateacc = true;
            }
            return response()->json([
                'status'=> 201,
                'message' => 'Compte créé avec succès',
                'account' => $account,
                'access' => $access ? $access->acs_accounttype : 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout du compte.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function modif(Request $request)
    {
        // Valider les données de la requête
        $validatedData = $request->validate([
            'acc_id' => 'required|string',
            'acc_studentnum' => 'required|integer',
            'dept_id' => 'nullable|integer',
            'acc_mail' => 'string',
            'acc_toeic' => 'required|integer',
        ]);

        // Trouver le compte
        $account = Account::find($validatedData['acc_id']);

        if (!$account) {
            return response()->json(['error' => 'Compte introuvable'], 404);
        }

        // Mettre à jour les propriétés du compte
        $account->acc_studentnum = $validatedData['acc_studentnum'];
        $account->dept_id = isset($validatedData['dept_id']) ? $validatedData['dept_id'] : null;
        $account->acc_mail = $validatedData['acc_mail'] ?? $account->acc_mail;
        $account->acc_toeic = $validatedData['acc_toeic'];

        // Sauvegarder les modifications
        $account->save();

        return response()->json(['status' => 200, 'message' => 'Le compte a été modifié avec succès.']);
    }
    
       
    public function login($id)
    {
        $account = Account::find($id);

        if (!$account) {
            return response()->json(['message' => 'Compte non trouvé.'], 404);
        }

        $account->acc_lastlogin = DB::raw('NOW()');
        $account->save();

        $succes = Account::findOrFail($id);
        return new AccountResource($succes);
    }

    public function changeDept($acc_id, $dept_id)
    {
        
        $account = Account::where('acc_id', $acc_id)->first();
        
        if (!$account) {
            return response()->json(['message' => 'Le compte n\'a pas été trouvé.'], 404);
        }
        $account->dept_id = $dept_id;
        $account->save();
    
        return response()->json(['status' => 200, 'message' => 'Le département de '.$acc_id.' a été modifié avec succès.']);
    }


    public function removeDeptByLogin($acc_id)
    {
        
        $account = Account::where('acc_id', $acc_id)->first();
        
        if (!$account) {
            return response()->json(['message' => 'Le compte n\'a pas été trouvé.'], 404);
        }
        $account->dept_id = null;
        $account->save();
    
        return response()->json(['status' => 202, 'message' => 'Le département de '.$acc_id.' a été supprimé avec succès.']);
    }

    public function compldossier(Request $request)
    {
        try {
            
            $validatedData = $request->validate([
                'acc_id' => 'required|string',
                'acc_studentnum' => 'required|string',
                'dept_id' => 'required|integer',
                'acc_amenagement' => 'required|boolean',
                'acc_amenagementdesc' => 'nullable|string',
                'acc_consent' => 'required|boolean',
            ]);


            $account = Account::where('acc_id', $validatedData['acc_id'])->first();
        
            if (!$account) {
                return response()->json(['message' => 'Le compte n\'a pas été trouvé.'], 404);
            }

            $account->acc_studentnum = $validatedData['acc_studentnum'];
            $account->dept_id = $validatedData['dept_id'];
            $account->acc_amenagement = $validatedData['acc_amenagement'];
            if(isset($validatedData['acc_amenagementdesc'])){
                $account->acc_amenagementdesc = $validatedData['acc_amenagementdesc'];
            }
            $account->acc_consent = $validatedData['acc_consent'];
            $account->acc_validateacc = true;

            $account->save();

            return response()->json(['status'=> 201 ,'message' => 'Compl dossier']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout du compte.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        // Recevoir les IDs en format JSON
        $idsJson = $request->input('ids');
    
        // Décoder les IDs JSON en tableau PHP
        $idsArray = json_decode($idsJson, true);
    
    
        // Vérifier que nous avons bien un tableau
        if (!is_array($idsArray)) {
            return response()->json(['error' => 'Invalid parameter format'], 400);
        }
    
        // Passer les IDs au constructeur de l'export
        return Excel::download(new AccountExport($idsArray), 'etudiants.csv');
    }
}
