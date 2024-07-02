<?php

namespace App\Http\Resources\Treasury\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'businessName' => $this->businessName,
            'cuit' => $this->cuit,
            'taxCondition' => $this->taxCondition ? $this->taxCondition->name : null,
            'idTC' => $this->idTC,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'addressNumber' => $this->addressNumber,
            'floor' => $this->floor,
            'block' => $this->block,
            'postalCode' => $this->postalCode,
            'district' => $this->district,
            'notes' => $this->notes,
            'incomeTax' => $this->incomeTax,
            'socialTax' => $this->socialTax,
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
