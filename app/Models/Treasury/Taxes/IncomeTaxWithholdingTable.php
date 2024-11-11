<?php

namespace App\Models\Treasury\Taxes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeTaxWithholdingTable extends Model {
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'idCat',
        'table',
    ];
}
