<?php

namespace App\Models\Treasury\Voucher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubtypeSupplierRelationship extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'idSubtype',
        'idSupplier',
        'idUserRelated',
        'related_at',
    ];
}
