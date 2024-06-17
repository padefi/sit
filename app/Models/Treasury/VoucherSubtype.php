<?php

namespace App\Models\Treasury;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherSubtype extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'idUserCreated',
        'created_at',
        'idUserUpdated',
        'updated_at',
        'status',
    ];

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }

    public function userUpdated() {
        return $this->belongsTo(User::class, 'idUserUpdated');
    }

    public function expenses() {
        return $this->belongsToMany(VoucherExpense::class, 'subtype_expense_relationship', 'idSubtype', 'idExpense')
        ->withPivot('idUserRelated', 'related_at');
    }
}
