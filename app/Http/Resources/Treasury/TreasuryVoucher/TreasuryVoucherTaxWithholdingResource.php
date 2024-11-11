<?php

namespace App\Http\Resources\Treasury\TreasuryVoucher;

use App\Http\Resources\Treasury\Voucher\VoucherResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreasuryVoucherTaxWithholdingResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            // 'voucher' => $this->voucher ? new VoucherResource($this->voucher) : null,
            'originalVoucher' => $this->originalVoucher ? new TreasuryVoucherResource($this->originalVoucher) : null,
            // 'newVoucher' => $this->newVoucher ? new TreasuryVoucherResource($this->newVoucher) : null,
            'taxType' => $this->taxType ? [
                'id' => $this->taxType->id,
                'name' => $this->taxType->name,
            ] : null,
            'amount' => $this->amount,
            'userCreated' => $this->userCreated ? [
                'name' => $this->userCreated->name,
                'surname' => $this->userCreated->surname,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }
}
