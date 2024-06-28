<?php

namespace App\Models\Treasury\Bank;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccountType extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
