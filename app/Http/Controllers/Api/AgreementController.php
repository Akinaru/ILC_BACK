<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\AgreementResource;
use App\Models\Agreement;
use App\Models\DepartmentAgreement;
use App\Models\Isced;
use App\Models\Component;
use App\Models\Favoris;
use App\Models\WishAgreement;
use App\Models\Arbitrage;
use App\Models\University;
use App\Models\PartnerCountry;
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
                'agree_nbplace' => 'required|integer',
                'agree_typeaccord' => 'required|string',
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
    

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'agree_lien' => 'string|nullable',
                'agree_description' => 'string|nullable',
                'agree_nbplace' => 'required|integer',
                'agree_typeaccord' => 'required|string',
                'isc_id' => 'required_without:newisced.isc_code|integer',
                'newisced.isc_code' => 'required_without:isc_id|string',
                'newisced.isc_name' => 'required_without:isc_id|string',

                'comp_id' => 'required_without:newcompo.comp_name|integer',
                'newcompo.comp_name' => 'required_without:comp_id|string',
                'newcompo.comp_shortname' => 'required_without:comp_id|string',

                'univ_id' => 'required_without:newuniv.univ_name|integer',
                'newuniv.univ_name' => 'required_without:univ_id|string',
                'newuniv.univ_city' => 'required_without:univ_id|string',

                'newuniv.parco_id' => 'integer|nullable',
                'newuniv.parco_name' => 'string|nullable',
                'newuniv.parco_code' => 'string|nullable',
            ]);

            $agreement = Agreement::findOrFail($id);

            if (isset($validatedData['newisced'])) {
                $newIsced = new Isced();
                $newIsced->isc_code = $validatedData['newisced']['isc_code'];
                $newIsced->isc_name = $validatedData['newisced']['isc_name'];
                $newIsced->save();

                $iscId = $newIsced->isc_id;
            } else {
                $iscId = $validatedData['isc_id'];
            }

            if (isset($validatedData['newcompo'])) {
                $newCompo = new Component();
                $newCompo->comp_name = $validatedData['newcompo']['comp_name'];
                $newCompo->comp_shortname = $validatedData['newcompo']['comp_shortname'];
                $newCompo->save();

                $compoId = $newCompo->comp_id;
            } else {
                $compoId = $validatedData['comp_id'];
            }

            if (isset($validatedData['newuniv'])) {
                if (isset($validatedData['newuniv']['parco_id'])) {
                    $parcoId = $validatedData['newuniv']['parco_id'];
                } else {
                    $newParco = new PartnerCountry();
                    $newParco->parco_name = $validatedData['newuniv']['parco_name'];
                    $newParco->parco_code = $validatedData['newuniv']['parco_code'];
                    $newParco->save();
                    $parcoId = $newParco->parco_id;
                }
                $newUniv = new University();
                $newUniv->univ_name = $validatedData['newuniv']['univ_name'];
                $newUniv->univ_city = $validatedData['newuniv']['univ_city'];
                $newUniv->parco_id = $parcoId;
                $newUniv->save();

                $univId = $newUniv->univ_id;
            } else {
                $univId = $validatedData['univ_id'];
            }

            $agreement->isc_id = $iscId;
            $agreement->comp_id = $compoId;
            $agreement->univ_id = $univId;
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

        
    public function deleteById($id)
    {
        
        $agreement = Agreement::find($id);
        
        if (!$agreement) {
            return response()->json(['message' => 'Accord non trouvée.'], 404);
        }
        $departmentAgreements = DepartmentAgreement::where('agree_id', $agreement->agree_id)->get();
        foreach ($departmentAgreements as $departmentAgreement) {
            $departmentAgreement->delete();
        }

        // Suppression des favoris
        $favoris = Favoris::all();
        foreach ($favoris as $fav){
            if($fav->agree_id == $agreement->agree_id){
                $fav->delete();
            }
        }

        //Suppression des voeux
        $voeux = WishAgreement::all();
        foreach($voeux as $voeu){
            if($voeu->wsha_one == $id){
                $voeu->wsha_one = null;
            }
            if($voeu->wsha_two == $id){
                $voeu->wsha_two = null;
            }
            if($voeu->wsha_three == $id){
                $voeu->wsha_three = null;
            }
            if($voeu->wsha_four == $id){
                $voeu->wsha_four = null;
            }
            if($voeu->wsha_five == $id){
                $voeu->wsha_five = null;
            }
            if($voeu->wsha_six == $id){
                $voeu->wsha_six = null;
            }
            $voeu->save();
            
            // Vérifier si tous les champs sont null
            if (is_null($voeu->wsha_one) && 
                is_null($voeu->wsha_two) && 
                is_null($voeu->wsha_three) && 
                is_null($voeu->wsha_four) && 
                is_null($voeu->wsha_five) && 
                is_null($voeu->wsha_six)) 
            {
                // Supprimer l'enregistrement si tous les champs sont null
                $voeu->delete();
            }
        }

        // Suppression des arbitrages liés
        $arbitrages = Arbitrage::where('agree_id', $agreement->agree_id)->get();
        foreach ($arbitrages as $arbitrage) {
            $arbitrage->delete();
        }

        $agreement->delete();

        return response()->json(['status' => 202, 'message' => 'l\'Accord a été supprimé avec succès.']);
    }

    public function export()
    {
        return Excel::download(new AgreementExport, 'accords.csv');
    }

}