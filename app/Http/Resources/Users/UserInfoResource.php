<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserInfoResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'userCreated' => $this->userCreated ? [
                'name' => $this->userCreated->name,
                'surname' => $this->userCreated->surname,
            ] : null,
            'userUpdated' => $this->userUpdated ? [
                'name' => $this->userUpdated->name,
                'surname' => $this->userUpdated->surname,
            ] : null,
            'userConfirmed' => $this->userConfirmed ? [
                'name' => $this->userConfirmed->name,
                'surname' => $this->userConfirmed->surname,
            ] : null,
            'userVoided' => $this->userVoided ? [
                'name' => $this->userVoided->name,
                'surname' => $this->userVoided->surname,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'confirmed_at' => $this->confirmed_at ? $this->confirmed_at : null,
            'voided_at' => $this->voided_at ? $this->voided_at : null,
        ];
    }
}
