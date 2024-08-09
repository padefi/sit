<?php

namespace App\Http\Resources\Treasury\Voucher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherItemResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'idVoucher' => $this->idVoucher,
            'description' => $this->description,
            'amount' => $this->amount,
            'VatRate' => $this->VatRate ? [
                'name' => $this->VatRate->name,
            ] : null,
            'subtotalAmount' => $this->subtotalAmount,
        ];
    }
}
