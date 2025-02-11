<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'act_id' => $this->resource->act_id,
            'act_description' => $this->resource->act_description,
            'act_date' => $this->resource->act_date,
            'act_type' => $this->resource->act_type,
            'agree_id' => $this->resource->agree_id,
            'dept_id' => $this->resource->dept_id,
            'art_id' => $this->resource->art_id,
            'evt_id' => $this->resource->evt_id,

            'acc_fullname' => $this->resource->account ? $this->resource->account->acc_fullname : null,
            'department' => $this->resource->department,
            'agreement' => $this->resource->agreement,
            'event' => $this->resource->event,
            'article' => $this->resource->article,
            'access' => $this->resource->access,
            'admin' => $this->resource->admin,
        ];
    }
}
