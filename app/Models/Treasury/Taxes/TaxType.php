<?php

namespace App\Models\Treasury\Taxes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxType extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
