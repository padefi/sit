<?php

namespace App\Models\Treasury\TreasuryVoucher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryVoucherStatus extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
