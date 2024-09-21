<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'art_id' => $this->resource->art_id, 
            'art_title' => $this->resource->art_title, 
            'art_description' => $this->resource->art_description, 
            'art_lastmodif' => $this->resource->art_lastmodif, 
            'art_creationdate' => $this->resource->art_creationdate, 
            'art_pin' => (bool) $this->resource->art_pin,
            'art_image' => $this->resource->art_image
        ];
    }
}
