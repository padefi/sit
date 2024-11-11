<?php

namespace App\Models\Treasury\Voucher;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoidedVoucher extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'idVoucher',
        'notes',
        'idUserVoided',
        'voided_at',
    ];

    /* public function vouchers() {
        return $this->belongsTo (Voucher::class, 'idVoucher');
    } */

    public function userVoided() {
        return $this->belongsTo(User::class, 'idUserVoided');
    }
}
