<?php

namespace App\Models\Treasury\Bank;

use App\Models\Treasury\Bank\Bank;
use App\Models\Treasury\Bank\BankAccountType;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model {
    use HasFactory;

    protected $fillable = [
        'idBank',
        'idAT',
        'accountNumber',
        'cbu',
        'alias',
        'idUserCreated',
        'created_at',
        'idUserUpdated',
        'updated_at',
        'status',
    ];

    public function bank() {
        return $this->belongsTo(Bank::class, 'idBank');
    }

    public function accountType() {
        return $this->belongsTo(BankAccountType::class, 'idAT');
    }

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }

    public function userUpdated() {
        return $this->belongsTo(User::class, 'idUserUpdated');
    }
}
