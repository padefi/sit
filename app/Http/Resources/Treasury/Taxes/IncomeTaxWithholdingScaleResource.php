<?php

namespace App\Http\Resources\Treasury\Taxes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeTaxWithholdingScaleResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'idCat' => $this->idCat,
            'category' => $this->category->name ?? null,
            'rate' => $this->rate,
            'minAmount' => $this->minAmount,
            'maxAmount' => $this->maxAmount,
            'fixedAmount' => $this->fixedAmount,
            'startAt' => $this->startAt,
            'endAt' => $this->endAt,
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
