<?php

namespace App\Models\Treasury\Voucher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceType extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function invoiceTypeCodes() {
        return $this->belongsToMany(InvoiceTypeCode::class, 'invoice_type_invoice_type_code_relationships', 'idIT', 'idITCode');
    }
}
