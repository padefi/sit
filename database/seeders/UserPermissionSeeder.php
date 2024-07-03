<?php

namespace Database\Seeders;

use App\Models\Users\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class UserPermissionSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $admin = User::where('username', 'admin')->first();
        $admin->givePermissionTo(Permission::all());

        $jperez = User::where('username', 'jperez')->first();
        $jperez->givePermissionTo(Permission::where('name', 'not like', '%create users%')
            ->where('name', 'not like', '%edit users%')
            ->get());

        $jgonzalez = User::where('username', 'jgonzalez')->first();
        $jgonzalez->givePermissionTo(Permission::where(function ($query) {
            $query->where('name', 'not like', '%users%')
                ->where('name', 'like', '%view%')
                ->orWhere('name', 'like', '%suppliers%')
                ->orWhere('name', 'like', '%vouchers%');
        })->get());

        $rgomez = User::where('username', 'rgomez')->first();
        $rgomez->givePermissionTo(Permission::where(function ($query) {
            $query->where('name', 'like', '%view suppliers%')
                ->orWhere('name', 'like', '%view vouchers%');
        })->get());
    }
}
