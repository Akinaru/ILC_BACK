<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IscedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'isc_id' => $this->resource->isc_id,
            'isc_code' => $this->resource->isc_code,
            'isc_name' => $this->resource->isc_name,
        ];
    }
}
