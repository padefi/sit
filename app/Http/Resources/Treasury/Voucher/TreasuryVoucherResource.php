<?php

namespace App\Http\Resources\Treasury\Voucher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TreasuryVoucherResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'voucherType' => $this->voucherType ? [
                'id' => $this->voucherType->id,
                'name' => $this->voucherType->name,
            ] : null,
            'supplier' => $this->supplier,
            'paymentMethod' => $this->paymentMethod ? [
                'id' => $this->paymentMethod->id,
                'name' => $this->paymentMethod->name,
            ] : null,
            'bankAccount' => $this->bankAccount,
            'voucherStatus' => $this->voucherStatus ? [
                'id' => $this->voucherStatus->id,
                'name' => $this->voucherStatus->name,
            ] : null,
            'amount' => $this->amount,
            'incomeTaxAmount' => $this->incomeTaxAmount,
            'socialTaxAmount' => $this->socialTaxAmount,
            'vatTaxAmount' => $this->vatTaxAmount,
            'totalAmount' => $this->totalAmount,
            'notes' => $this->notes,
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
