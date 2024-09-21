<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'acc_id' => $this->resource->acc_id,
            'acs_accounttype' => $this->resource->acs_accounttype,
            'account' => $this->resource->account ? new AccountResource($this->resource->account) : null

        ];
    }
}
