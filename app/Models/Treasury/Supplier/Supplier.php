<?php

namespace App\Models\Treasury\Supplier;

use App\Models\Treasury\Taxes\Category;
use App\Models\Treasury\Taxes\VatCondition;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'businessName',
        'cuit',
        'idVC',
        'idCat',
        'street',
        'streetNumber',
        'floor',
        'apartment',
        'city',
        'state',
        'postalCode',
        'osm_id',
        'phone',
        'email',
        'notes',
        'incomeTax',
        'socialTax',
        'vatTax',
        'idUserCreated',
        'idUserUpdated',
    ];

    public function vatCondition() {
        return $this->belongsTo(VatCondition::class, 'idVC');
    }

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
