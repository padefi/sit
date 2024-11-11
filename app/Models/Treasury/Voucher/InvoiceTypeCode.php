<?php

namespace App\Models\Treasury\Voucher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTypeCode extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
