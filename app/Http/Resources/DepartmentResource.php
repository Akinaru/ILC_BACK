<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'dept_id' => $this->resource->dept_id,
            'dept_name' => $this->resource->dept_name,
            'dept_shortname' => $this->resource->dept_shortname,
            'dept_color' => $this->resource->dept_color,
            'component' => $this->resource->component,
            // 'agreements' => AgreementResource::collection($this->agreements)
            
        ];
    }
}
