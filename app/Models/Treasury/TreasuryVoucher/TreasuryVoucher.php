<?php

namespace App\Models\Treasury\TreasuryVoucher;

use App\Models\Treasury\Bank\BankAccount;
use App\Models\Treasury\Supplier\Supplier;
use App\Models\Treasury\TreasuryVoucher\TreasuryVoucherStatus;
use App\Models\Treasury\Voucher\VoucherToTreasury;
use App\Models\Treasury\Voucher\VoucherType;
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
        'paymentDate',
        'idUserCreated',
        'idUserUpdated',
        'idUserConfirmed',
        'idUserVoided',
        'updated_at',
        'confirmed_at',
        'voided_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'incomeTaxAmount' => 'float',
        'socialTaxAmount' => 'float',
        'vatTaxAmount' => 'float',
        'totalAmount' => 'float',
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

    public function voucherToTreasury() {
        return $this->hasMany(VoucherToTreasury::class, 'idTV');
    }

    public function treasuryVoucherTaxWithholding() {
        return $this->hasOne(TreasuryVoucherTaxWithholding::class, 'idNTV');
    }

    public function treasuryCustomVoucher() {
        return $this->hasOne(TreasuryCustomVoucher::class, 'idTV');
    }

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }

    public function userUpdated() {
        return $this->belongsTo(User::class, 'idUserUpdated');
    }

    public function userConfirmed() {
        return $this->belongsTo(User::class, 'idUserConfirmed');
    }

    public function userVoided() {
        return $this->belongsTo(User::class, 'idUserVoided');
    }
}
