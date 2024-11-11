<?php

namespace App\Models\Treasury\TreasuryVoucher;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoidedTreasuryVoucher extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'idTV',
        'notes',
        'idUserVoided',
        'voided_at',
    ];

    /* public function treasuryVoucher() {
        return $this->belongsTo(TreasuryVoucher::class, 'idTV');
    } */

    public function userVoided() {
        return $this->belongsTo(User::class, 'idUserVoided');
    }
}
