<?php

namespace App\Http\Resources\Treasury;

use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherSubtypeResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
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
            'expenses' => $this->expenses->map(function ($expense) {
                $userRelated = $expense->pivot->idUserRelated ? User::find($expense->pivot->idUserRelated) : null;
                return [
                    'id' => $expense->id,
                    'name' => $expense->name,
                    'status' => $expense->status,
                    'userRelated' => $userRelated ? [
                        'name' => $userRelated->name,
                        'surname' => $userRelated->surname,
                    ] : null,
                    'related_at' => $expense->pivot->related_at,
                ];
            }),
            'status' => $this->status,
        ];
    }
}
