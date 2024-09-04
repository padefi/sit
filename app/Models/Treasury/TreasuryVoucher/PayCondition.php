<?php

namespace App\Models\Treasury\TreasuryVoucher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayCondition extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
