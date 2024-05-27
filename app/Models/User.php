<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'surname',
        'name',
        'email',
        'username',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function generateUsername($name, $surname) {
        $baseUsername = strtolower(substr($name, 0, 1) . trim($surname));
        $username = $baseUsername;
        $counter = 1;

        while (self::where('username', $username)->exists()) {
            $username = $baseUsername . ++$counter;
        }

        return $username;
    }

    public static function assignDefaultPermissions($user, $roleName) {
        $defaultPermissions = [
            'admin' => Permission::all(),
            'tesorero' => Permission::where('name', 'not like', '%create users%')
                ->where('name', 'not like', '%edit users%')
                ->get(),
            'auxiliar' => Permission::where(function ($query) {
                $query->where('name', 'not like', '%users%')
                    ->where('name', 'like', '%view%')
                    ->orWhere('name', 'like', '%providers%')
                    ->orWhere('name', 'like', '%vouchers%');
            })->get(),
            'administrativo' => Permission::where(function ($query) {
                $query->where('name', 'like', '%view providers%')
                    ->orWhere('name', 'like', '%view vouchers%');
            })->get(),
        ];

        if (isset($defaultPermissions[$roleName])) {
            $permissions = $defaultPermissions[$roleName];
            $user->syncPermissions($permissions);
        } else {
            throw ValidationException::withMessages([
                'message' => trans('Sin registros predeterminados para el rol: ' . $roleName),
            ]);
        }
    }
}
