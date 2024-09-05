<?php

namespace App\Models\Treasury\TreasuryVoucher;

use App\Models\Treasury\Bank\BankAccount;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecksTransaction extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'idBA',
        'idTV',
        'number',
        'idUserConfirmed',
        'idUserVoided',
        'confirmed_at',
        'voided_at',
        'status',
    ];

    public function bankAccount() {
        return $this->belongsTo(BankAccount::class, 'idBA');
    }

    public function treasuryVoucher() {
        return $this->belongsTo(TreasuryVoucher::class, 'idTV');
    }

    public function userConfirmed() {
        return $this->belongsTo(User::class, 'idUserConfirmed');
    }

    public function userVoided() {
        return $this->belongsTo(User::class, 'idUserVoided');
    }
}
