<?php

namespace App\Models\Treasury\Voucher;

use App\Models\Treasury\Supplier\Supplier;
use App\Models\Treasury\TreasuryVoucher\PayCondition;
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
        'invoiceDueDate',
        'idPC',
        'notes',
        'totalAmount',
        'idUserCreated',
        'idUserUpdated',
        'updated_at',
        'status',
    ];

    protected $casts = [
        'totalAmount' => 'float',
    ];

    public function voucherSupplier() {
        return $this->belongsTo(Supplier::class, 'idSupplier');
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

    public function voucherToTreasury() {
        return $this->hasMany(VoucherToTreasury::class, 'idVoucher');
    }

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }

    public function userUpdated() {
        return $this->belongsTo(User::class, 'idUserUpdated');
    }
}
