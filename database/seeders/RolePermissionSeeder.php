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

        $treasurerRole = Role::findByName('tesorero');
        $treasurerPermissions = Permission::where('name', 'not like', '%create users%')
            ->where('name', 'not like', '%edit users%')
            ->get();
        $treasurerRole->syncPermissions($treasurerPermissions);

        $auxiliaryRole = Role::findByName('auxiliar');
        $auxiliaryPermissions = Permission::where('name', 'not like', '%users%')->get();
        $auxiliaryRole->syncPermissions($auxiliaryPermissions);

        $administrativeRole = Role::findByName('administrativo');
        $AdministrativePermissions = Permission::where(function ($query) {
            $query->where('name', 'like', '%providers%')
                ->orWhere('name', 'like', '%vouchers%');
        })->get();
        $administrativeRole->syncPermissions($AdministrativePermissions);
    }
}
