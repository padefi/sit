<?php

namespace App\Models\Treasury\Voucher;

use App\Models\Treasury\Taxes\VatRate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherItem extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'idVoucher',
        'description',
        'amount',
        'idVat',
        'subtotalAmount',
    ];

    public function VatRate() {
        return $this->belongsTo(VatRate::class, 'idVat');
    }
}
