<?php

namespace App\Models\Treasury\TreasuryVoucher;

use App\Models\Treasury\Taxes\TaxType;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryVoucherTaxWithholding extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        // 'idVoucher',
        'idOTV',
        'idNTV',
        'idTT',
        'amount',
        'idUserCreated',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function originalVoucher() {
        return $this->belongsTo(TreasuryVoucher::class, 'idOTV');
    }

    /* public function newVoucher() {
        return $this->belongsTo(TreasuryVoucher::class, 'idNTV');
    } */

    public function taxType() {
        return $this->belongsTo(TaxType::class, 'idTT');
    }

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }
}
