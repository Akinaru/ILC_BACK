<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgreementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'agree_id' => $this->resource->agree_id,
            'agree_lien' => $this->resource->agree_lien,
            'agree_nbplace' => $this->resource->agree_nbplace,
            'agree_typeaccord' => $this->resource->agree_typeaccord,
            'agree_note' => $this->resource->agree_note,
            'agree_description' => $this->resource->agree_description,
            'isced' => $this->when($this->resource->isced, function () {
                return new IscedResource($this->resource->isced);
            }, null),
            'university' => $this->when($this->resource->university, function () {
                return new UniversityResource($this->resource->university);
            }, null),
            'component' => $this->when($this->resource->component, function () {
                return new ComponentResource($this->resource->component);
            }, null),
            'departments' => $this->resource->departments->sortBy('dept_shortname')->values(), // Trie par dept_shortname
            'partnercountry' => $this->when($this->resource->university && $this->resource->university->partnercountry, function () {
                return new PartnerCountryResource($this->resource->university->partnercountry);
            }, null) ?? $this->whenLoaded('partnercountry', function () {
                return new PartnerCountryResource($this->resource->partnercountry);
            }),
        ];
    }
    
}
