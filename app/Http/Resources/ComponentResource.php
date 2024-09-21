<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComponentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'comp_id' => $this->resource->comp_id,
            'comp_name' => $this->resource->comp_name,
            'comp_shortname' => $this->resource->comp_shortname,
            'departments' => $this->resource->departments
        ];
    }
}
