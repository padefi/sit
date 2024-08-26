<?php

namespace App\Http\Resources\Treasury\Voucher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource {
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
            'voucherSubtype' => $this->voucherSubtype ? [
                'id' => $this->voucherSubtype->id,
                'name' => $this->voucherSubtype->name,
            ] : null,
            'voucherExpense' => $this->voucherExpense ? [
                'id' => $this->voucherExpense->id,
                'name' => $this->voucherExpense->name,
            ] : null,
            'invoiceType' => $this->invoiceType ? [
                'id' => $this->invoiceType->id,
                'name' => $this->invoiceType->name,
            ] : null,
            'invoiceTypeCode' => $this->invoiceTypeCode ? [
                'id' => $this->invoiceTypeCode->id,
                'name' => $this->invoiceTypeCode->name,
            ] : null,
            'pointOfNumber' => $this->pointOfNumber,
            'invoiceNumber' => $this->invoiceNumber,
            'invoiceDate' => $this->invoiceDate,
            'invoiceDueDate' => $this->invoiceDueDate,
            'payCondition' => $this->payCondition ? [
                'id' => $this->payCondition->id,
                'name' => $this->payCondition->name,
            ] : null,
            'notes' => $this->notes,
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'amount' => $item->amount,
                    'VatRate' => $item->VatRate,
                    'subtotalAmount' => $item->subtotalAmount,
                ];
            }),
            'totalAmount' => $this->totalAmount,
            'pendingToPay' => $this->pendingToPay ?? $this->totalAmount,
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
            'status' => $this->status,
        ];
    }
}
