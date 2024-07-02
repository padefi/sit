<?php

namespace App\Http\Resources\Treasury\Taxes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxConditionResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
