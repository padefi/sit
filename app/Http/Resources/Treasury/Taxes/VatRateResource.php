<?php

namespace App\Http\Resources\Treasury\Taxes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VatRateResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'rate' => $this->rate,
        ];
    }
}
