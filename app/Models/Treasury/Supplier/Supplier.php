<?php

namespace App\Models\Treasury\Supplier;

use App\Models\Treasury\Taxes\Category;
use App\Models\Treasury\Taxes\VatCondition;
use App\Models\Treasury\Voucher\VoucherSubtype;
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
        'country',
        'postalCode',
        'osm_id',
        'latitude',
        'longitude',
        'phone',
        'email',
        'cbu',
        'notes',
        'incomeTaxWithholding',
        'socialTax',
        'vatTax',
        'idUserCreated',
        'idUserUpdated',
        'updated_at',
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

    public function userRelated() {
        return $this->belongsTo(User::class, 'idUserRelated');
    }

    public function subtypes() {
        return $this->belongsToMany(VoucherSubtype::class, 'subtype_supplier_relationships', 'idSupplier', 'idSubtype')
            ->withPivot('idUserRelated', 'related_at')
            ->with('userRelated');
    }
}
