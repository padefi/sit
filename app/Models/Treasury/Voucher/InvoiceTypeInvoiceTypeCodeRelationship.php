<?php

namespace App\Models\Treasury\Voucher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTypeInvoiceTypeCodeRelationship extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'idIT',
        'idITCode',
    ];
}
