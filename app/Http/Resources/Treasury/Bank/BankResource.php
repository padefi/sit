<?php

namespace App\Http\Resources\Treasury\Bank;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'street' => $this->street,
            'streetNumber' => $this->streetNumber,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'osm_id' => $this->osm_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'postalCode' => $this->postalCode,
            'phone' => $this->phone,
            'email' => $this->email,
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
