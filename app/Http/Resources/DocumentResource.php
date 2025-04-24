<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'doc_id' => $this->resource->doc_id,
            'doc_name' => $this->resource->doc_name,
            'doc_path' => $this->resource->doc_path,
        ];
    }
}