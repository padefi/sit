<?php

namespace App\Http\Resources\Treasury\TreasuryVoucher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankTransactionResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'treasuryVoucher' => new TreasuryVoucherResource($this->treasuryVoucher),
        ];
    }
}
