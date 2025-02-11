<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishAgreementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'acc_id' => $this->acc_id,
            'agree_one' => new AgreementResource($this->agree_one),
            'agree_two' => new AgreementResource($this->agree_two),
            'agree_three' => new AgreementResource($this->agree_three),
            'agree_four' => new AgreementResource($this->agree_four),
            'agree_five' => new AgreementResource($this->agree_five),
            'agree_six' => new AgreementResource($this->agree_six),
        ];
    }
}
