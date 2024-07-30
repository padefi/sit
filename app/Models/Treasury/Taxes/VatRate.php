<?php

namespace App\Models\Treasury\Taxes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VatRate extends Model {
    use HasFactory;

    protected $fillable = [
        'rate',
    ];
}
