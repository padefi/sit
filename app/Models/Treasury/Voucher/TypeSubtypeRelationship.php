<?php

namespace App\Models\Treasury\Voucher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeSubtypeRelationship extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'idType',
        'idSubtype',
        'idUserRelated',
        'related_at',
    ];
}
