<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerCountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'parco_id' => $this->resource->parco_id,
            'parco_name' => $this->resource->parco_name,
            'parco_code' => $this->resource->parco_code,
        ];
    }
}
