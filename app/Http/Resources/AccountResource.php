<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Agreement;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $wishes = $this->resource->wishes;
    
        // Initialiser le tableau des souhaits et le nombre de souhaits non nuls à 0
        $wishesArray = [];
        $wishCount = 0;
    
        if ($wishes) {
            // Compter le nombre de vœux non nuls
            $wishCount = collect([
                $wishes->wsha_one,
                $wishes->wsha_two,
                $wishes->wsha_three,
                $wishes->wsha_four,
                $wishes->wsha_five,
                $wishes->wsha_six,
            ])->filter(function ($wish) {
                return !is_null($wish);
            })->count();
    
            // Convertir l'objet wishes en tableau
            $wishesArray = $wishes->toArray();
        }
    
        // Ajouter le champ 'count' au tableau des souhaits
        $wishesArray['count'] = $wishCount;
    
        $roleInfo = $this->getRoleInfo();
        $docCount = $this->getFileCount();

        $accessResponse = null;
        if ($this->resource->access) {
            $accessResponse = [
                'access' => $this->resource->access,
                'count' => 1,
            ];
        } else {
            $accessResponse = [
                'count' => 0,
            ];
        }

        // Vérifier si la destination existe
        $destination = null;
        if ($this->resource->destination_id && 
            $this->resource->destination && 
            $this->resource->destination->agree_id) {
            $destination = new AgreementResource($this->resource->destination);
        }

        // Vérifier si l'arbitrage existe et si l'accord associé existe
        $arbitrage = null;
        if ($this->resource->arbitrage && 
            $this->resource->arbitrage->agreement_id && 
            $this->resource->arbitrage->agreement && 
            $this->resource->arbitrage->agreement->agree_id) {
            $arbitrage = [
                ...(new AgreementResource($this->resource->arbitrage->agreement))->toArray($request),
                'status' => \App\Models\Administration::find(1)->adm_arbitragetemporaire ?? false
            ];
        }

        return [
            'acc_id' => $this->resource->acc_id,
            'acc_fullname' => $this->resource->acc_fullname,
            'acc_lastlogin' => $this->resource->acc_lastlogin,
            'acc_studentnum' => $this->resource->acc_studentnum,
            'acc_amenagement' => $this->resource->acc_amenagement,
            'acc_amenagemendesc' => $this->resource->acc_amenagementdesc,
            'acc_anneemobilite' => $this->resource->acc_anneemobilite,
            'acc_temoignage' => $this->resource->acc_temoignage,
            'acc_validechoixcours' => (bool) $this->resource->acc_validechoixcours,
            'acc_toeic' => $this->resource->acc_toeic,
            'acc_mail' => $this->resource->acc_mail,
            'acc_parcours' => $this->resource->acc_parcours,
            'acc_validateacc' => (bool) $this->resource->acc_validateacc,
            'acc_arbitragefait' => (bool) $this->resource->acc_arbitragefait,
            'department' => $this->resource->department,
            'access' => $accessResponse,
            'role' => $roleInfo,
            'documents' => $docCount,
            'destination' => $destination,
            'arbitrage' => $arbitrage,
            'wishes' => $wishesArray,
        ];
    }
}