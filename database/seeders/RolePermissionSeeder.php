<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $adminRole = Role::findByName('admin');
        $allPermissions = Permission::all();
        $adminRole->syncPermissions($allPermissions);

        $tesoreroRole = Role::findByName('tesorero');
        $tesoreroPermissions = Permission::where('name', 'not like', '%user%')->get();
        $tesoreroRole->syncPermissions($tesoreroPermissions);

        $userRole = Role::findByName('usuario');
        $specificPermissions = Permission::where(function ($query) {
            $query->where('name', 'like', '%providers%')
                  ->orWhere('name', 'like', '%vouchers%');
        })->get();
        $userRole->syncPermissions($specificPermissions);
    }
}
