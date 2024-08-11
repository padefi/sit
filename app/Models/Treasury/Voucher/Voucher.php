<?php

namespace App\Models\Treasury\Voucher;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model {
    use HasFactory;

    protected $fillable = [
        'idSupplier',
        'idType',
        'idSubtype',
        'idExpense',
        'idIT',
        'idITCode',
        'pointOfNumber',
        'invoiceNumber',
        'invoiceDate',
        'invoicePaymentDate',
        'idPC',
        'notes',
        'totalAmount',
        'idUserCreated',
        'idUserUpdated',
        'updated_at',
        'status',
    ];

    public function voucherType() {
        return $this->belongsTo(VoucherType::class, 'idType');
    }

    public function voucherSubtype() {
        return $this->belongsTo(VoucherSubtype::class, 'idSubtype');
    }

    public function voucherExpense() {
        return $this->belongsTo(VoucherExpense::class, 'idExpense');
    }

    public function invoiceType() {
        return $this->belongsTo(InvoiceType::class, 'idIT');
    }

    public function invoiceTypeCode() {
        return $this->belongsTo(InvoiceTypeCode::class, 'idITCode');
    }

    public function payCondition() {
        return $this->belongsTo(PayCondition::class, 'idPC');
    }

    public function items() {
        return $this->hasMany(VoucherItem::class, 'idVoucher');
    }

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }

    public function userUpdated() {
        return $this->belongsTo(User::class, 'idUserUpdated');
    }
}
