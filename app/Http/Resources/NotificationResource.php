<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'not_id' => $this->resource->not_id,
            'not_date' => Carbon::parse($this->resource->not_date)->translatedFormat('l d M \à H\hi'),
            'not_message' => $this->resource->not_message,
            'not_vue' => (bool) $this->resource->not_vue,
            'envoyeur' => $this->resource->envoyeur ? new AccountResource($this->resource->envoyeur) : $this->resource->not_envoyeur,
            'receveur' => $this->resource->not_receveur,
        ];
    }
}
