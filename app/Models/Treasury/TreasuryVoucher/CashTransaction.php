<?php

namespace App\Models\Treasury\TreasuryVoucher;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'idTV',
        'amount',
        'idUserConfirmed',
        'idUserVoided',
        'confirmed_at',
        'voided_at',
        'status',
    ];

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
