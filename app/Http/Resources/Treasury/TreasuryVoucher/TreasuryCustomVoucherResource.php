<?php

namespace App\Http\Resources\Treasury\TreasuryVoucher;

use App\Http\Resources\Treasury\Voucher\VoucherResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreasuryCustomVoucherResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'supplier' => $this->supplier->id,
            'voucherType' => $this->voucherType ? [
                'id' => $this->voucherType->id,
                'name' => $this->voucherType->name,
            ] : null,
            'voucherSubtype' => $this->voucherSubtype ? [
                'id' => $this->voucherSubtype->id,
                'name' => $this->voucherSubtype->name,
            ] : null,
            'voucherExpense' => $this->voucherExpense ? [
                'id' => $this->voucherExpense->id,
                'name' => $this->voucherExpense->name,
            ] : null,
            'amount' => $this->amount,
            'notes' => $this->notes,
            'voucherDate' => $this->voucherDate,
            'userCreated' => $this->userCreated ? [
                'name' => $this->userCreated->name,
                'surname' => $this->userCreated->surname,
            ] : null,
            'userUpdated' => $this->userUpdated ? [
                'name' => $this->userUpdated->name,
                'surname' => $this->userUpdated->surname,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
