<?php

namespace App\Http\Resources\Treasury\Bank;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'accountNumber' => $this->accountNumber,
            'cbu' => $this->cbu,
            'alias' => $this->alias,
            'bank' => $this->bank ? [
                'id' => $this->bank->id,
                'name' => $this->bank->name,
            ] : null,
            'accountType' => $this->accountType ? [
                'id' => $this->accountType->id,
                'name' => $this->accountType->name,
            ] : null,
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
