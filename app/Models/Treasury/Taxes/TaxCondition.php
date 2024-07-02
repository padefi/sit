<?php

namespace App\Models\Treasury\Taxes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxCondition extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
