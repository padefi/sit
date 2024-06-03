<?php

namespace App\Models\Treasury;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherExpense extends Model {
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
}
