<?php

namespace App\Models\Treasury\Taxes;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialSecurityTaxWithholding extends Model {
    use HasFactory;

    protected $fillable = [
        'idCat',
        'rate',
        'minAmount',
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
