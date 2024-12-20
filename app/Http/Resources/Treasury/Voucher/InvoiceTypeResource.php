<?php

namespace App\Http\Resources\Treasury\Voucher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceTypeResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'invoiceTypeCodes' => $this->invoiceTypeCodes->map(function ($invoiceTypeCode) {
                return [
                    'id' => $invoiceTypeCode->id,
                    'name' => $invoiceTypeCode->name,
                ];
            }),
        ];
    }
}
