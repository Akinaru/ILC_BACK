<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\Access;
use App\Models\WishAgreement;
use App\Models\Favoris;
use App\Models\AcceptedAccount;
use App\Models\Arbitrage;
use Illuminate\Support\Facades\DB;
use App\Exports\AccountExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AccountController extends Controller
{
    public function index(){
        $accountCollection = AccountResource::collection(Account::all())->all();

        return response()->json([
            'accounts' => $accountCollection,
            'count' => Account::all()->count(),
        ]);
    }

    public function indexActuel()
    {
        $accounts = Account::where('acc_ancienetu', 0)->get();
        $accountCollection = AccountResource::collection($accounts)->all();

        return response()->json([
            'accounts' => $accountCollection,
            'count' => $accounts->count(),
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


    public function studentsFiltered(Request $request)
    {
        $departments = (array) $request->query('departments', []);
        $voeux = (array) $request->query('voeux', []);
        $annees = (array) $request->query('annees', []);
        $periodes = (array) $request->query('periodes', []);
        $documents = (array) $request->query('documents', []);
        $destinations = (array) $request->query('destinations', []);
        $autres = (array) $request->query('autres', []);
        $searchQuery = $request->query('search', null);

        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('perPage', 20));

        // Requête de base
        $query = Account::doesntHave('access');

        if (!empty($departments)) {
            $query->where(function ($q) use ($departments) {
                foreach ($departments as $dept) {
                    if ($dept === 'Aucun') {
                        $q->orWhereNull('dept_id');
                    } else {
                        $deptLower = strtolower($dept);
                        $q->orWhereHas('department', function ($q2) use ($deptLower) {
                            $q2->whereRaw('LOWER(dept_shortname) = ?', [$deptLower]);
                        });
                    }
                }
            });
        }

        if (!empty($voeux)) {
            $query->where(function ($q) use ($voeux) {
                foreach ($voeux as $v) {
                    if ($v === 'AuMoinsUn') {
                        $q->orWhereHas('wishes');
                    } elseif ($v === 'Aucun') {
                        $q->orWhereDoesntHave('wishes');
                    }
                }
            });
        }

        if ($searchQuery) {
            $like = '%' . strtolower($searchQuery) . '%';
            $query->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(acc_fullname) LIKE ?', [$like])
                ->orWhereRaw('LOWER(acc_id) LIKE ?', [$like]);
            });
        }


        if (!empty($annees)) {
            $query->whereIn('acc_anneemobilite', $annees);
        }

        if (!empty($periodes)) {
            $query->whereIn('acc_periodemobilite', $periodes);
        }

        if (!empty($destinations)) {
            $query->where(function ($q) use ($destinations) {
                foreach ($destinations as $dest) {
                    if ($dest === 'null') {
                        $q->orWhere(function ($subQ) {
                            $subQ->whereDoesntHave('arbitrage')
                                ->whereNull('acc_json_agreement');
                        });
                    } else {
                        $q->orWhere(function ($subQ) use ($dest) {
                            $subQ->whereHas('arbitrage', function ($q2) use ($dest) {
                                $q2->where('agree_id', $dest);
                            })->orWhereJsonContains('acc_json_agreement->agree_id', (int)$dest);
                        });
                    }
                }
            });
        }

        if (!empty($autres)) {
            $query->where(function ($q) use ($autres) {
                foreach ($autres as $val) {
                    if ($val === 'archivetrue') {
                        $q->orWhere('acc_ancienetu', true);
                    } elseif ($val === 'archivefalse') {
                        $q->orWhere('acc_ancienetu', false)->orWhereNull('acc_ancienetu');
                    }
                }
            });
        }

        $query->orderBy('acc_fullname');

        // Récupération de tous les comptes (non paginée)
        $accounts = $query->get();

        // Filtrage en PHP avec getFileCount
        if (!empty($documents)) {
            $accounts = $accounts->filter(function ($account) use ($documents) {
                $fileCount = $account->getFileCount()['count'] ?? 0;

                foreach ($documents as $doc) {
                    if ($doc === 'ChoixCoursValide' && $account->acc_validechoixcours) {
                        return true;
                    }
                    if (in_array($doc, ['0', '1', '2', '3'], true) && (string)$fileCount === $doc) {
                        return true;
                    }
                }

                return false;
            })->values();
        }


        // Pagination manuelle
        $total = $accounts->count();
        $results = $accounts->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $accountCollection = AccountResource::collection($paginated->items());

        return response()->json([
            'accounts' => $accountCollection,
            'count' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }





    public function studentsActuel()
    {
        $accountsWithoutAccess = Account::where('acc_ancienetu', false)
            ->doesntHave('access')
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
        try {
            $account = Account::findOrFail($login);
            return new AccountResource($account);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 404, 'error' => 'Compte introuvable']);
        }
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

    public function getByDeptActuel($dept_id)
    {
        $accounts = Account::where('dept_id', $dept_id)
                        ->where('acc_ancienetu', 0)
                        ->doesntHave('access')
                        ->get();

        $accountCollection = AccountResource::collection($accounts)->all();

        return response()->json([
            'accounts' => $accountCollection,
            'count' => $accounts->count(),
        ]);
    }

    public function getByDeptAncien($dept_id)
    {
        $accounts = Account::where('dept_id', $dept_id)
                           ->where('acc_ancienetu', 1)
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
            
            // Création du token pour l'aplli de messagerie
            $account->acc_tokenapplimsg = Str::upper(Str::random(8));

            $account->save();

            
            $account->tokens()->delete();

            // Créer le token
            $token = $account->createToken('auth-token')->plainTextToken;
    
            // Vérifier s'il existe un enregistrement Access associé à ce compte
            $access = Access::where('acc_id', $account->acc_id)->first();
            if($access){
                $account->acc_validateacc = true;
            }
            return response()->json([
                'status'=> 201,
                'message' => 'Compte créé avec succès',
                'account' => new AccountResource($account),
                'token' => $token,
                'access' => $access ? $access->acs_accounttype : 0,
                
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout du compte.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function temoignage(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'acc_id' => 'required|string',
                'acc_temoignage' => 'nullable|string',  // Changé pour permettre null
            ]);
    
            // Trouver le compte
            $account = Account::find($validatedData['acc_id']);
            
            if (!$account) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Compte non trouvé',
                ]);
            }
    
            // Mise à jour du témoignage
            $account->acc_temoignage = $validatedData['acc_temoignage'];
            $account->save();
    
            return response()->json([
                'status' => 200,
                'message' => 'Témoignage modifié avec succès !',
                'account' => $account,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la modification du témoignage.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function supprimerTemoignage(Request $request)
{
    try {
        $validatedData = $request->validate([
            'acc_id' => 'required|string',
        ]);

        // Trouver le compte
        $account = Account::find($validatedData['acc_id']);

        if (!$account) {
            return response()->json([
                'status' => 404,
                'message' => 'Compte non trouvé',
            ]);
        }

        // Suppression du témoignage
        $account->acc_temoignage = null;
        $account->save();

        return response()->json([
            'status' => 200,
            'message' => 'Témoignage supprimé avec succès.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Une erreur s\'est produite lors de la suppression du témoignage.',
            'message' => $e->getMessage()
        ], 500);
    }
}


    public function validateChoixCours($id)
    {
        $account = Account::find($id);
        
        if (!$account) {
            return response()->json(['status' => 404, 'message' => 'Compte non trouvé.']);
        }
        
        $account->acc_validechoixcours = !$account->acc_validechoixcours;
        $account->save();
        
        $succes = Account::findOrFail($id);
        return response()->json([
            'status' => 200,
            'message' => $account->acc_validechoixcours 
                ? 'Les choix de cours ont été validés avec succès.' 
                : 'La validation des choix de cours a été annulée.',
        ]);
    }


    public function modifEtu(Request $request)
    {
        // Valider les données de la requête
        $validatedData = $request->validate([
            'acc_id' => 'required|string',
            'acc_studentnum' => 'required|string',
            'acc_parcours' => 'nullable|string',
            'acc_mail' => 'string',
        ]);

        // Trouver le compte
        $account = Account::find($validatedData['acc_id']);

        if (!$account) {
            return response()->json(['error' => 'Compte introuvable'], 404);
        }

        // Mettre à jour les propriétés du compte
        $account->acc_studentnum = $validatedData['acc_studentnum'];
        $account->acc_parcours = $validatedData['acc_parcours'];
        $account->acc_mail = $validatedData['acc_mail'] ?? $account->acc_mail;

        // Sauvegarder les modifications
        $account->save();

        return response()->json(['status' => 200, 'message' => 'Le compte a été modifié avec succès.']);
    }

    public function modif(Request $request)
    {
        // Valider les données de la requête
        $validatedData = $request->validate([
            'acc_id' => 'required|string',
            'acc_studentnum' => 'required|string',
            'acc_anneemobilite' => 'nullable|string',
            'acc_periodemobilite' => 'nullable|integer',
            'dept_id' => 'nullable|integer',
            'acc_mail' => 'string',
            'acc_toeic' => 'required|integer',
            'acc_parcours' => 'nullable|string',
        ]);

        // Trouver le compte
        $account = Account::find($validatedData['acc_id']);

        if (!$account) {
            return response()->json(['error' => 'Compte introuvable'], 404);
        }

        // Mettre à jour les propriétés du compte
        $account->acc_studentnum = $validatedData['acc_studentnum'];
        $account->acc_anneemobilite = $validatedData['acc_anneemobilite'];
        $account->acc_periodemobilite = $validatedData['acc_periodemobilite'];
        $account->dept_id = isset($validatedData['dept_id']) ? $validatedData['dept_id'] : null;
        $account->acc_mail = $validatedData['acc_mail'] ?? $account->acc_mail;
        $account->acc_toeic = $validatedData['acc_toeic'];
        $account->acc_parcours = $validatedData['acc_parcours'];

        // Sauvegarder les modifications
        $account->save();

        return response()->json(['status' => 200, 'message' => 'Le compte a été modifié avec succès.']);
    }
    
    public function deleteById($id)
    {
        $account = Account::find($id);
    
        if (!$account) {
            return response()->json(['status' => 404, 'message' => 'Compte non trouvé.']);
        }
        
        // Suppression des accès
        Access::where('acc_id', $id)->delete();
        
        // Suppression des comptes acceptés
        AcceptedAccount::where('acc_id', $id)->delete();
        
        // Suppression de TOUS les vœux associés au compte (pas seulement le premier)
        WishAgreement::where('acc_id', $id)->delete();
        
        // Suppression de TOUS les arbitrages associés au compte (pas seulement le premier)
        Arbitrage::where('acc_id', $id)->delete();
        
        // Suppression des favoris associés au compte
        Favoris::where('acc_id', $id)->delete();
        
        // Suppression du compte
        $account->delete();
    
        // Appel des méthodes deletePerso dans le DocumentController
        $documentController = new DocumentsController();
        $documentController->deletePerso('choix_cours', 'choix_cours_'.$id);
        $documentController->deletePerso('contrat_peda', 'contrat_peda_'.$id);
        $documentController->deletePerso('releve_note', 'releve_note_'.$id);
    
        return response()->json(['status' => 202, 'message' => 'Le compte a été supprimé avec succès.']);
    }
    
    public function selfDelete(Request $request)
    {
        $validatedData = $request->validate([
            'acc_id' => 'required|string',
        ]);
        
        $request->user()->currentAccessToken()->delete();
        $id = $validatedData['acc_id'];
    
        $account = Account::find($id);
    
        if (!$account) {
            return response()->json(['status' => 404, 'message' => 'Compte non trouvé.']);
        }
        
        // Suppression des accès
        Access::where('acc_id', $id)->delete();
        
        // Suppression des comptes acceptés
        AcceptedAccount::where('acc_id', $id)->delete();
        
        // Suppression de TOUS les vœux associés au compte (pas seulement le premier)
        WishAgreement::where('acc_id', $id)->delete();
        
        // Suppression de TOUS les arbitrages associés au compte (pas seulement le premier)
        Arbitrage::where('acc_id', $id)->delete();
        
        // Suppression des favoris associés au compte
        Favoris::where('acc_id', $id)->delete();
        
        // Suppression du compte
        $account->delete();
    
        // Appel des méthodes deletePerso dans le DocumentController
        $documentController = new DocumentsController();
        $documentController->deletePerso('choix_cours', 'choix_cours_'.$id);
        $documentController->deletePerso('contrat_peda', 'contrat_peda_'.$id);
        $documentController->deletePerso('releve_note', 'releve_note_'.$id);
    
        return response()->json(['status' => 202, 'message' => 'Le compte a été supprimé avec succès.']);
    }
       
    public function login($acc_id)
    {
        $account = Account::find($acc_id);

        if (!$account) {
            return response()->json(['status' => 404, 'message' => 'Compte non trouvé.']);
        }

        $account->acc_lastlogin = DB::raw('NOW()');
        $account->save();

        $succes = Account::findOrFail($acc_id);
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
                'acc_anneemobilite' => 'required|string',
                'acc_periodemobilite' => 'required|integer',
                'acc_mail' => 'required|string',
                'acc_parcours' => 'nullable|string',
                'acc_consent' => 'required|boolean',
            ]);


            $account = Account::where('acc_id', $validatedData['acc_id'])->first();
        
            if (!$account) {
                return response()->json(['message' => 'Le compte n\'a pas été trouvé.'], 404);
            }

            $account->acc_studentnum = $validatedData['acc_studentnum'];
            $account->acc_mail = $validatedData['acc_mail'];
            $account->acc_anneemobilite = $validatedData['acc_anneemobilite'];
            $account->acc_periodemobilite = $validatedData['acc_periodemobilite'];
            $account->dept_id = $validatedData['dept_id'];
            if(isset($validatedData['acc_parcours'])){
                $account->acc_parcours = $validatedData['acc_parcours'];
            }
            $account->acc_consent = $validatedData['acc_consent'];
            $account->acc_validateacc = true;

            $account->save();
            

            return response()->json(['status'=> 201 ,'message' => 'Compl dossier', 'account' => new AccountResource($account)]);
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
