<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentAgreementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'deptagree_id' => $this->resource->deptagree_id,
            'dept_id' => $this->resource->dept_id,
            'agree_id' => $this->resource->agree_id,
            'deptagree_valide' => $this->resource->deptagree_valide,
        ];
    }
}
