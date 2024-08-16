<?php

namespace App\Models\Treasury\Voucher;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherToTreasury extends Model {
    use HasFactory;

    public $timestamps = false;

    public $table = 'voucher_to_treasury';

    protected $fillable = [
        'idVoucher',
        'idTV',
        'amount',
        'idUserSent',
        'related_at',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function vouchers() {
        return $this->belongsTo (Voucher::class, 'idVoucher');
    }

    public function treasuryVoucher() {
        return $this->belongsTo(TreasuryVoucher::class, 'idTV');
    }

    public function userSent() {
        return $this->belongsTo(User::class, 'idUserSent');
    }
}
