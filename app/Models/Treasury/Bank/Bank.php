<?php

namespace App\Models\Treasury\Bank;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'street',
        'streetNumber',
        'city',
        'state',
        'country',
        'postalCode',
        'osm_id',
        'latitude',
        'longitude',
        'phone',
        'email',
        'notes',
        'idUserCreated',
        'created_at',
        'idUserUpdated',
        'updated_at',
    ];

    public function userCreated() {
        return $this->belongsTo(User::class, 'idUserCreated');
    }

    public function userUpdated() {
        return $this->belongsTo(User::class, 'idUserUpdated');
    }
}
