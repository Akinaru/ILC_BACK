<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            ])->filter(function ($wish) {
                return !is_null($wish);
            })->count();
    
            // Convertir l'objet wishes en tableau
            $wishesArray = $wishes->toArray();
        }
    
        // Ajouter le champ 'count' au tableau des souhaits
        $wishesArray['count'] = $wishCount;
    
        $roleInfo = $this->getRoleInfo();

        return [
            'acc_id' => $this->resource->acc_id,
            'acc_fullname' => $this->resource->acc_fullname,
            'acc_lastlogin' => $this->resource->acc_lastlogin,
            'acc_studentnum' => $this->resource->acc_studentnum,
            'acc_amenagement' => $this->resource->acc_amenagement,
            'acc_amenagemendesc' => $this->resource->acc_amenagementdesc,
            'acc_toeic' => $this->resource->acc_toeic,
            'acc_mail' => $this->resource->acc_mail,
            'acc_validateacc' => (bool) $this->resource->acc_validateacc,
            'department' => $this->resource->department,
            'access' => $this->resource->access,
            'role' => $roleInfo,
            'arbitrage' => $this->resource->arbitrage 
            ? new AgreementResource($this->resource->arbitrage->agreement) 
            : null,
            'wishes' => $wishesArray,
        ];
    }
    
    
    
}
