<?php

namespace App\Models\Treasury\Voucher;

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

    protected $hidden = [
        'idUserCreated',
        'idUserUpdated'
    ];

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }

    public function userUpdated() {
        return $this->belongsTo(User::class, 'idUserUpdated');
    }

    public function userRelated() {
        return $this->belongsTo(User::class, 'idUserRelated');
    }

    public function expenses() {
        return $this->belongsToMany(VoucherExpense::class, 'subtype_expense_relationships', 'idSubtype', 'idExpense')
            ->withPivot('idUserRelated', 'related_at')
            ->with('userRelated');
    }
}
