<?php

namespace App\Http\Resources\Treasury\Voucher;

use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherExpenseResource extends JsonResource {
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
            'subtypes' => $this->subtypes->map(function ($subtype) {
                $userRelated = $subtype->pivot->idUserRelated ? User::find($subtype->pivot->idUserRelated) : null;
                return [
                    'id' => $subtype->id,
                    'name' => $subtype->name,
                    'status' => $subtype->status,
                    'userRelated' => $userRelated ? [
                        'name' => $userRelated->name,
                        'surname' => $userRelated->surname,
                    ] : null,
                    'related_at' => $subtype->pivot->related_at,
                ];
            }),
            'status' => $this->status,
        ];
    }
}
