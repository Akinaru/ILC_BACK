<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AgreementResource;
use App\Http\Resources\WishAgreementResource;
use App\Http\Resources\FavorisResource;
use App\Models\Agreement;
use App\Models\DepartmentAgreement;
use App\Models\Isced;
use App\Models\Component;
use App\Models\Favoris;
use App\Models\WishAgreement;
use App\Models\Arbitrage;
use App\Models\University;
use App\Models\PartnerCountry;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use App\Exports\AgreementExport;
use Maatwebsite\Excel\Facades\Excel;


class AgreementController extends Controller
{
    public function index()
    {
        $agreements = Agreement::all();
    
        $sortedAgreements = $agreements->sortBy(function ($agreement) {
            // Vérifie si 'partnercountry' et 'parco_name' existent
            return $agreement->university && $agreement->university->partnercountry
                ? $agreement->university->partnercountry->parco_name
                : '';
        });
    
        $sortedAgreements = $sortedAgreements->values();
    
        $agreementCollection = AgreementResource::collection($sortedAgreements);
    
        return response()->json([
            'agreements' => $agreementCollection,
            'count' => $agreements->count(),
        ]);
    }
    public function agreementHome()
    {
        $account = auth()->user()->load(['favoris.agreement', 'wishes']);
    
        // Récupérer les IDs des favoris
        $favorisIds = $account->favoris->pluck('agree_id')->toArray();
    
        // Récupérer les IDs des vœux
        $voeuIds = collect([
            $account->wishes->wsha_one ?? null,
            $account->wishes->wsha_two ?? null,
            $account->wishes->wsha_three ?? null,
            $account->wishes->wsha_four ?? null,
            $account->wishes->wsha_five ?? null,
            $account->wishes->wsha_six ?? null,
        ])->filter()->toArray();
    
        // Fusionner et retirer les doublons
        $allIds = array_unique(array_merge($favorisIds, $voeuIds));
    
        // Charger tous les accords nécessaires
        $agreements = Agreement::whereIn('agree_id', $allIds)->get();
    
        return response()->json([
            'agreements' => AgreementResource::collection($agreements),
        ]);
    }

public function random(Request $request)
{
    try {
        $dept_id = $request->query('dept_id');
        $exclude_agree_id = $request->query('agree_id');
        $univ_id = $request->query('univ_id');

        $agreementsQuery = Agreement::query();

        if ($dept_id) {
            $agreementIds = DepartmentAgreement::where('dept_id', $dept_id)->pluck('agree_id')->toArray();

            $agreementsQuery->whereIn('agree_id', $agreementIds);

            if (count($agreementIds) < 15) {
                $additionalCount = 15 - count($agreementIds);
                $additionalAgreementIds = Agreement::whereNotIn('agree_id', $agreementIds)
                                                    ->inRandomOrder()
                                                    ->limit($additionalCount)
                                                    ->pluck('agree_id')
                                                    ->toArray();

                $agreementIds = array_merge($agreementIds, $additionalAgreementIds);
                $agreementsQuery->whereIn('agree_id', $agreementIds);
            }
        } else {
            $agreementsQuery->inRandomOrder()->limit(10);
        }

        if ($univ_id) {
            $agreementsQuery->orWhere('univ_id', $univ_id);
        }

        if ($exclude_agree_id) {
            $agreementsQuery->where('agree_id', '!=', $exclude_agree_id);
        }

        $agreements = $agreementsQuery->get();

        $agreementsResource = AgreementResource::collection($agreements);

        return response()->json(['status' => 201, 'agreements' => $agreementsResource]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Une erreur s\'est produite lors de la récupération des accords.',
            'message' => $e->getMessage()
        ], 500);
    }
}

    
    

public function getById($id)
{
    $agreement = Agreement::findOrFail($id);

    // Assurez-vous que les relations sont présentes avant de les utiliser dans la ressource
    $resource = new AgreementResource($agreement);

    return response()->json(['agreement' => $resource]);
}




    public function store(Request $request)
    {
        try {
            // Validation des données
            $validatedData = $request->validate([
                'agree_lien' => 'string',
                'agree_description' => 'string',
                'agree_nbplace' => 'integer',
                'agree_typeaccord' => 'string',
                'isc_id' => 'required_without:newisced.isc_code|integer',
                'newisced.isc_code' => 'required_without:isc_id|string',
                'newisced.isc_name' => 'required_without:isc_id|string',
    
                'comp_id' => 'required_without:newcompo.comp_name|integer',
                'newcompo.comp_name' => 'required_without:comp_id|string',
                'newcompo.comp_shortname' => 'required_without:comp_id|string',
    
                'univ_id' => 'required_without:newuniv.univ_name|integer',
                'newuniv.univ_name' => 'required_without:univ_id|string',
                'newuniv.univ_city' => 'required_without:univ_id|string',
    
                'newuniv.parco_id' => 'integer',
                'newuniv.parco_name' => 'string',
                'newuniv.parco_code' => 'string',
            ]);
    
            // Gestion du champ ISC (si -1, mettre null)
            if (isset($validatedData['newisced']) && $validatedData['newisced']['isc_code'] != '-1') {          
                $newIsced = new Isced();
                $newIsced->isc_code = $validatedData['newisced']['isc_code'];
                $newIsced->isc_name = $validatedData['newisced']['isc_name'];
                $newIsced->save();
            
                $iscId = $newIsced->isc_id;
            } elseif (isset($validatedData['isc_id']) && $validatedData['isc_id'] != -1) {
                $iscId = $validatedData['isc_id'];
            } else {
                $iscId = null; // Mettre null si isc_code ou isc_id vaut -1
            }
    
            // Gestion du champ Component (si -1, mettre null)
            if (isset($validatedData['newcompo']) && $validatedData['newcompo']['comp_name'] != '-1') {          
                $newCompo = new Component();
                $newCompo->comp_name = $validatedData['newcompo']['comp_name'];
                $newCompo->comp_shortname = $validatedData['newcompo']['comp_shortname'];
                $newCompo->save();
            
                $compoId = $newCompo->comp_id;
            } elseif (isset($validatedData['comp_id']) && $validatedData['comp_id'] != -1) {
                $compoId = $validatedData['comp_id'];
            } else {
                $compoId = null; // Mettre null si comp_id vaut -1
            }
    
            // Gestion du champ University (si -1, mettre null)
            if (isset($validatedData['newuniv']) && $validatedData['newuniv']['univ_name'] != '-1') {    
                
                if (isset($validatedData['newuniv']['parco_id']) && $validatedData['newuniv']['parco_id'] != -1) {   
                    $parcoId = $validatedData['newuniv']['parco_id'];
                } else {
                    if (isset($validatedData['newuniv']['parco_name']) && isset($validatedData['newuniv']['parco_code'])) {
                        $newParco = new PartnerCountry();
                        $newParco->parco_name = $validatedData['newuniv']['parco_name'];
                        $newParco->parco_code = $validatedData['newuniv']['parco_code'];
                        $newParco->save();
                        $parcoId = $newParco->parco_id;
                    } else {
                        $parcoId = null;
                    }
                }
    
                $newUniv = new University();
                $newUniv->univ_name = $validatedData['newuniv']['univ_name'];
                $newUniv->univ_city = $validatedData['newuniv']['univ_city'];
                $newUniv->parco_id = $parcoId;
                $newUniv->save();
            
                $univId = $newUniv->univ_id;
            } elseif (isset($validatedData['univ_id']) && $validatedData['univ_id'] != -1) {
                $univId = $validatedData['univ_id'];
            } else {
                $univId = null; // Mettre null si univ_id vaut -1
            }
    
            // Créer l'accord avec des champs null si nécessaire
            $agreement = new Agreement();
            $agreement->isc_id = $iscId;
            $agreement->comp_id = $compoId;
            $agreement->univ_id = $univId;
            $agreement->agree_nbplace = $validatedData['agree_nbplace'];
    
            // Si le code ISC, component ou université est -1, mettre certains champs à null
            if ($iscId === null || $compoId === null || $univId === null) {
                $agreement->agree_lien = null;
                $agreement->agree_description = null;
                $agreement->agree_typeaccord = null;
            } else {
                $agreement->agree_typeaccord = $validatedData['agree_typeaccord'];
                if (isset($validatedData['agree_lien'])) {   
                    $agreement->agree_lien = $validatedData['agree_lien'];
                }
                if (isset($validatedData['agree_description'])) {   
                    $agreement->agree_description = $validatedData['agree_description'];
                }
            }
            
            $agreement->save();
    
            // Reprendre l'accord pour récupérer les détails de l'université
            $succes = Agreement::findOrFail($agreement->agree_id);
    
            return response()->json(['status'=> 201 ,'message' => 'L\'accord a été ajouté avec succès.', 'agreement' => new AgreementResource($succes)]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout de l\'accord.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function storeImport(Request $request)
    {
        try {
            // Validation des données
            $validatedData = $request->validate([
                'agreements' => 'required|array',
                'agreements.*.agree_lien' => 'nullable|string',
                'agreements.*.agree_description' => 'nullable|string',
                'agreements.*.agree_nbplace' => 'nullable|integer',
                'agreements.*.agree_typeaccord' => 'nullable|string',
                'agreements.*.isced.isc_id' => 'nullable|integer',
                'agreements.*.isced.isc_code' => 'nullable|string',
                'agreements.*.isced.isc_name' => 'nullable|string',
                'agreements.*.component.comp_id' => 'nullable|integer',
                'agreements.*.component.comp_name' => 'nullable|string',
                'agreements.*.component.comp_shortname' => 'nullable|string',
                'agreements.*.university.univ_id' => 'nullable|integer',
                'agreements.*.university.univ_name' => 'nullable|string',
                'agreements.*.university.univ_city' => 'nullable|string',
                'agreements.*.partnercountry.parco_id' => 'nullable|integer',
                'agreements.*.partnercountry.parco_name' => 'nullable|string',
                'agreements.*.partnercountry.parco_code' => 'nullable|string',
                'agreements.*.departments' => 'nullable|array',
                'agreements.*.departments.*.dept_id' => 'nullable|integer',
                'agreements.*.departments.*.dept_shortname' => 'nullable|string',
            ]);
    
            // Itération sur chaque accord
            foreach ($validatedData['agreements'] as $data) {
                // Gestion du champ ISC
                $iscId = null;
                if (!empty($data['isced']['isc_id'])) {
                    $iscId = $data['isced']['isc_id'];
                } elseif (!empty($data['isced']['isc_code']) && !empty($data['isced']['isc_name'])) {
                    $isc = new Isced();
                    $isc->isc_code = $data['isced']['isc_code'];
                    $isc->isc_name = $data['isced']['isc_name'];
                    $isc->save();
                    $iscId = $isc->isc_id;
                }
    
                // Gestion de la composante
                $compId = null;
                if (!empty($data['component']['comp_id'])) {
                    $compId = $data['component']['comp_id'];
                } elseif (!empty($data['component']['comp_name']) && !empty($data['component']['comp_shortname'])) {
                    $comp = new Component();
                    $comp->comp_name = $data['component']['comp_name'];
                    $comp->comp_shortname = $data['component']['comp_shortname'];
                    $comp->save();
                    $compId = $comp->comp_id;
                }
    
                // Gestion de l'université
                $univId = null;
                if (!empty($data['university']['univ_id'])) {
                    $univId = $data['university']['univ_id'];
                } elseif (!empty($data['university']['univ_name']) && !empty($data['university']['univ_city'])) {
                    $univ = new University();
                    $univ->univ_name = $data['university']['univ_name'];
                    $univ->univ_city = $data['university']['univ_city'];
                    $univ->parco_id = $data['partnercountry']['parco_id'] ?? null;
                    $univ->save();
                    $univId = $univ->univ_id;
                }
    
                // Gestion du pays partenaire
                $parcoId = null;
                if (!empty($data['partnercountry']['parco_id'])) {
                    $parcoId = $data['partnercountry']['parco_id'];
                } elseif (!empty($data['partnercountry']['parco_name']) && !empty($data['partnercountry']['parco_code'])) {
                    $parco = new PartnerCountry();
                    $parco->parco_name = $data['partnercountry']['parco_name'];
                    $parco->parco_code = $data['partnercountry']['parco_code'];
                    $parco->save();
                    $parcoId = $parco->parco_id;
                }
    
                // Vérifier si univ_id est null
                if (is_null($univId)) {
                    continue; // Passer à l'accord suivant
                }
    
                // Créer l'accord
                $agreement = new Agreement();
                $agreement->agree_nbplace = $data['agree_nbplace'];
                $agreement->agree_typeaccord = $data['agree_typeaccord'];
                $agreement->agree_lien = $data['agree_lien'];
                $agreement->agree_description = $data['agree_description'];
                $agreement->isc_id = $iscId;
                $agreement->comp_id = $compId;
                $agreement->univ_id = $univId;
                $agreement->save();
    
                // Lier les départements à l'accord
                if (!empty($data['departments'])) {
                    foreach ($data['departments'] as $dept) {
                        if (!empty($dept['dept_id'])) {
                            $deptAgree = new DepartmentAgreement();
                            $deptAgree->deptagree_id = null; // Gérer l'auto-incrémentation si nécessaire
                            $deptAgree->agree_id = $agreement->agree_id;
                            $deptAgree->dept_id = $dept['dept_id'];
                            $deptAgree->deptagree_valide = true; // Ajuster si nécessaire
                            $deptAgree->save();
                        } elseif (!empty($dept['dept_shortname'])) {
                            // Logique pour gérer les nouveaux départements, si nécessaire
                        }
                    }
                }
            }
    
            return response()->json(['status' => 201, 'message' => 'Les accords ont été ajoutés avec succès.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de l\'ajout des accords.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
    

    


    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'agree_lien' => 'string|nullable',
                'agree_description' => 'string|nullable',
                'agree_note' => 'string|nullable',
                'agree_nbplace' => 'integer',
                'agree_typeaccord' => 'string',
                'isc_id' => 'integer|nullable',
                'comp_id' => 'integer|nullable',
                'univ_id' => 'integer|nullable',

                'newuniv.parco_id' => 'integer|nullable',
                'newuniv.parco_name' => 'string|nullable',
                'newuniv.parco_code' => 'string|nullable',
            ]);

            $agreement = Agreement::findOrFail($id);

            $agreement->isc_id = $validatedData['isc_id'];
            $agreement->comp_id = $validatedData['comp_id'];
            $agreement->univ_id = $validatedData['univ_id'];
            $agreement->agree_nbplace = $validatedData['agree_nbplace'];
            $agreement->agree_typeaccord = $validatedData['agree_typeaccord'];
            if (isset($validatedData['agree_lien'])) {
                $agreement->agree_lien = $validatedData['agree_lien'];
            } else {
                $agreement->agree_lien = null;
            }
            if (isset($validatedData['agree_description'])) {
                $agreement->agree_description = $validatedData['agree_description'];
            } else {
                $agreement->agree_description = null;
            }
            if (isset($validatedData['agree_note'])) {
                $agreement->agree_note = $validatedData['agree_note'];
            } else {
                $agreement->agree_note = null;
            }
            $agreement->save();

            $succes = Agreement::findOrFail($agreement->agree_id);

            return response()->json(['status' => 200, 'message' => 'L\'accord a été modifié avec succès.', 'agreement' => new AgreementResource($succes)]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la modification de l\'accord.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteAll(Request $request)
    {
        try {
            // Récupérer tous les accords
            $agreements = Agreement::all();
    
            if ($agreements->isEmpty()) {
                return response()->json(['message' => 'Aucun accord à supprimer.'], 400);
            }
    
            foreach ($agreements as $agreement) {
                $this->deleteById($agreement->agree_id);
            }
    
            return response()->json(['status' => 202, 'message' => 'Tous les accords ont été supprimés avec succès.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la suppression des accords.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    

        
    public function deleteById($id)
    {
        $agreement = Agreement::find($id);
        
        if (!$agreement) {
            return response()->json(['message' => 'Accord non trouvée.'], 404);
        }
    
        // Suppression des relations département-accord
        DepartmentAgreement::where('agree_id', $agreement->agree_id)->delete();
    
        // Suppression des favoris
        Favoris::where('agree_id', $agreement->agree_id)->delete();
    
        // Mise à jour des voeux
        $wishAgreements = WishAgreement::where('wsha_one', $id)
            ->orWhere('wsha_two', $id)
            ->orWhere('wsha_three', $id)
            ->orWhere('wsha_four', $id)
            ->orWhere('wsha_five', $id)
            ->orWhere('wsha_six', $id)
            ->get();
    
        foreach ($wishAgreements as $wishAgreement) {
            // Mise à jour des champs contenant l'ID de l'accord
            if ($wishAgreement->wsha_one == $id) {
                $wishAgreement->wsha_one = null;
            }
            if ($wishAgreement->wsha_two == $id) {
                $wishAgreement->wsha_two = null;
            }
            if ($wishAgreement->wsha_three == $id) {
                $wishAgreement->wsha_three = null;
            }
            if ($wishAgreement->wsha_four == $id) {
                $wishAgreement->wsha_four = null;
            }
            if ($wishAgreement->wsha_five == $id) {
                $wishAgreement->wsha_five = null;
            }
            if ($wishAgreement->wsha_six == $id) {
                $wishAgreement->wsha_six = null;
            }
    
            // Enregistrement des modifications
            $wishAgreement->save();
            
            // Vérification si tous les champs sont null pour supprimer l'enregistrement
            if (is_null($wishAgreement->wsha_one) && 
                is_null($wishAgreement->wsha_two) && 
                is_null($wishAgreement->wsha_three) && 
                is_null($wishAgreement->wsha_four) && 
                is_null($wishAgreement->wsha_five) && 
                is_null($wishAgreement->wsha_six)) 
            {
                $wishAgreement->delete();
            }
        }
    
        // Suppression des arbitrages liés
        Arbitrage::where('agree_id', $agreement->agree_id)->delete();
    
        // Suppression de l'accord
        $agreement->delete();
    
        return response()->json(['status' => 202, 'message' => 'l\'Accord a été supprimé avec succès.']);
    }

    public function export()
    {
        return Excel::download(new AgreementExport, 'accords.csv');
    }

}