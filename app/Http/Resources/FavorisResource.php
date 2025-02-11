<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavorisResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'fav_id' => $this->resource->fav_id,
            'acc_id' => $this->resource->acc_id,
            'agree_id' => $this->resource->agree_id,
        ];
    }
}
