<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArbitrageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'agree_id' => $this->resource->agree_id,
            'arb_pos' => $this->resource->arb_pos,
            'status' => \App\Models\Administration::find(1)->adm_arbitragetemporaire ?? false,
            'agreement' => new AgreementResource($this->resource->agreement),
            'account' => new AccountResource($this->resource->account),
        ];
    }
}
