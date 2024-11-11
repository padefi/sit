<?php

namespace App\Http\Resources\Treasury\TreasuryVoucher;

use App\Http\Resources\Users\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoidedTreasuryVoucherResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'notes' => $this->notes,
            'userVoided' => [
                'name' => $this->userVoided->name,
                'surname' => $this->userVoided->surname,
            ],
            'voided_at' => $this->voided_at,
        ];
    }
}
