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
            'agree_id' => $this->when($this->resource->agree_id !== null, function () {
                return $this->resource->agree_id;
            }, null),
            
            'agree_lien' => $this->when($this->resource->agree_lien !== null, function () {
                return $this->resource->agree_lien;
            }, null),
            
            'agree_nbplace' => $this->when($this->resource->agree_nbplace !== null, function () {
                return $this->resource->agree_nbplace;
            }, null),
            
            'agree_typeaccord' => $this->when($this->resource->agree_typeaccord !== null, function () {
                return $this->resource->agree_typeaccord;
            }, null),
            
            'agree_note' => $this->when($this->resource->agree_note !== null, function () {
                return $this->resource->agree_note;
            }, null),
            
            'agree_description' => $this->when($this->resource->agree_description !== null, function () {
                return $this->resource->agree_description;
            }, null),
            
            'isced' => $this->when($this->resource->isced, function () {
                return new IscedResource($this->resource->isced);
            }, null),
            
            'university' => $this->when($this->resource->university, function () {
                return new UniversityResource($this->resource->university);
            }, null),
            
            'component' => $this->when($this->resource->component, function () {
                return new ComponentResource($this->resource->component);
            }, null),
            
            'departments' => $this->when($this->resource->departments !== null && $this->resource->departments->count() > 0, function () {
                return $this->resource->departments->sortBy('dept_shortname')->values();
            }, []),
            
            'partnercountry' => $this->when(
                $this->resource->university && $this->resource->university->partnercountry, 
                function () {
                    return new PartnerCountryResource($this->resource->university->partnercountry);
                }, 
                function () {
                    return $this->whenLoaded('partnercountry', function () {
                        return $this->resource->partnercountry ? new PartnerCountryResource($this->resource->partnercountry) : null;
                    }, null);
                }
            ),
        ];
    }
}