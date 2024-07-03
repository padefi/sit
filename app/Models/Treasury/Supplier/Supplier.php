<?php

namespace App\Models\Treasury\Supplier;

use App\Models\Treasury\Taxes\TaxCondition;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'businessName',
        'cuit',
        'idTC',        
        'street',
        'streetNumber',
        'floor',
        'city',
        'phone',
        'email',
        'postalCode',
        'notes',
        'incomeTax',
        'socialTax',
        'idUserCreated',
        'idUserUpdated',
    ];

    public function taxCondition() {
        return $this->belongsTo(TaxCondition::class, 'idTC');
    }

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }

    public function userUpdated() {
        return $this->belongsTo(User::class, 'idUserUpdated');
    }
}
