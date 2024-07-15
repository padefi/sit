<?php

namespace App\Models\Treasury\Taxes;

use App\Models\Treasury\Taxes\Category;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeTaxWithholdingScale extends Model {
    use HasFactory;

    protected $table = 'income_tax_withholdings_scales';

    protected $fillable = [
        'idCat',
        'rate',
        'minAmount',
        'maxAmount',
        'fixedAmount',
        'startAt',
        'endAt',
        'idUserCreated',
        'idUserUpdated',
    ];

    public function category() {
        return $this->belongsTo(Category::class, 'idCat');
    }

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }

    public function userUpdated() {
        return $this->belongsTo(User::class, 'idUserUpdated');
    }
}
