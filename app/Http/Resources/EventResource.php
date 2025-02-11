<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'evt_id' => $this->resource->evt_id,
            'evt_name' => $this->resource->evt_name,
            'evt_description' => $this->resource->evt_description,
            'evt_datetime' => $this->resource->evt_datetime,
            'theme' => $this->resource->theme,
        ];
    }
}
