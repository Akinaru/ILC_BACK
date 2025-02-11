<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UniversityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'univ_id' => $this->resource->univ_id,
            'univ_name' => $this->resource->univ_name,
            'univ_city' => $this->resource->univ_city,
            'univ_mail' => $this->resource->univ_mail,
            'univ_adress' => $this->resource->univ_adress,
            'parco_id' => $this->resource->parco_id,
            'partnercountry' => $this->resource->partnercountry,
        ];
    }
}
