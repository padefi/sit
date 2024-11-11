<?php

namespace App\Models\Treasury\TreasuryVoucher;

use App\Models\Treasury\Supplier\Supplier;
use App\Models\Treasury\Voucher\VoucherExpense;
use App\Models\Treasury\Voucher\VoucherSubtype;
use App\Models\Treasury\Voucher\VoucherType;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasuryCustomVoucher extends Model {
    use HasFactory;

    protected $fillable = [
        'idTV',
        'idSupplier',
        'idType',
        'idSubtype',
        'idExpense',
        'amount',
        'notes',
        'voucherDate',
        'idUserCreated',
        'idUserUpdated',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function treasuryVoucher() {
        return $this->belongsTo(TreasuryVoucher::class, 'idTV');
    }

    public function supplier() {
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

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }

    public function userUpdated() {
        return $this->belongsTo(User::class, 'idUserUpdated');
    }
}
