<?php

namespace App\Models\Treasury\Voucher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubtypeExpenseRelationship extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'idSubtype',
        'idExpense',
        'idUserRelated',
        'related_at',
    ];
}
