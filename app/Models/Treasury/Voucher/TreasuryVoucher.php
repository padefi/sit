<?php

namespace App\Models\Treasury\Voucher;

use App\Models\Treasury\Bank\BankAccount;
use App\Models\Treasury\Supplier\Supplier;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryVoucher extends Model {
    use HasFactory;

    protected $fillable = [
        'idType',
        'idSupplier',
        'idPM',
        'idBA',
        'idVS',
        'amount',
        'incomeTaxAmount',
        'socialTaxAmount',
        'vatTaxAmount',
        'totalAmount',
        'notes',
        'idUserCreated',
        'idUserUpdated',
        'updated_at',
    ];

    public function voucherType() {
        return $this->belongsTo(VoucherType::class, 'idType');
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class, 'idSupplier');
    }

    public function paymentMethod() {
        return $this->belongsTo(PaymentMethod::class, 'idPM');
    }

    public function bankAccount() {
        return $this->belongsTo(BankAccount::class, 'idBA');
    }

    public function voucherStatus() {
        return $this->belongsTo(TreasuryVoucherStatus::class, 'idVS');
    }

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }

    public function userUpdated() {
        return $this->belongsTo(User::class, 'idUserUpdated');
    }
}
