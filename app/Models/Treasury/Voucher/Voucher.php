<?php

namespace App\Models\Treasury\Voucher;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model {
    use HasFactory;

    protected $fillable = [
        'idIT',
        'idITCode',
        'pointOfNumber',
        'invoiceNumber',
        'invoiceDate',
        'invoicePaymentDate',
        'idPC',
        'idType',
        'idSubtype',
        'idExpense',
        'notes',
        'totalAmount',
        'idUserCreated',
        'idUserUpdated',
        'updated_at',
    ];

    public function invoiceType() {
        return $this->belongsTo(InvoiceType::class, 'idIT');
    }

    public function invoiceTypeCode() {
        return $this->belongsTo(InvoiceTypeCode::class, 'idITCode');
    }

    public function payCondition() {
        return $this->belongsTo(PayCondition::class, 'idPC');
    }

    public function voucherType() {
        return $this->belongsTo(VoucherType::class, 'idType');
    }

    public function voucherSubtype() {
        return $this->belongsTo(VoucherSubtype::class, 'idSubtype');
    }
    
    public function voucherExpense() {
        return $this->belongsTo(VoucherExpense::class, 'idExpense');
    }

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }

    public function userUpdated() {
        return $this->belongsTo(User::class, 'idUserUpdated');
    }
}
