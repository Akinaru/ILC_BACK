<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdministrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'adm_datelimite_printemps' => $this->resource->adm_datelimite_printemps,
            'adm_datelimite_automne' => $this->resource->adm_datelimite_automne,
            'adm_arbitragetemporaire' => $this->resource->adm_arbitragetemporaire,
        ];
    }
}
