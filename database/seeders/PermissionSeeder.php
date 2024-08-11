<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $permissions = [
            ['name' => 'view users', 'description' => 'Usuarios', 'show' => false],
            ['name' => 'create users', 'description' => 'Usuarios', 'show' => false],
            ['name' => 'edit users', 'description' => 'Usuarios', 'show' => false],
            ['name' => 'permission users', 'description' => 'Usuarios', 'show' => false],
            ['name' => 'view voucher types', 'description' => 'Tipo', 'show' => false],
            ['name' => 'create voucher types', 'description' => 'Tipo', 'show' => false],
            ['name' => 'edit voucher types', 'description' => 'Tipo', 'show' => false],
            ['name' => 'relationship voucher types', 'description' => 'Tipo', 'show' => false],
            ['name' => 'view voucher subtypes', 'description' => 'Subtipo', 'show' => true],
            ['name' => 'create voucher subtypes', 'description' => 'Subtipo', 'show' => true],
            ['name' => 'edit voucher subtypes', 'description' => 'Subtipo', 'show' => true],
            ['name' => 'relationship voucher subtypes', 'description' => 'Subtipo', 'show' => false],
            ['name' => 'view voucher expenses', 'description' => 'Gastos', 'show' => true],
            ['name' => 'create voucher expenses', 'description' => 'Gastos', 'show' => true],
            ['name' => 'edit voucher expenses', 'description' => 'Gastos', 'show' => true],
            ['name' => 'view banks', 'description' => 'Bancos', 'show' => true],
            ['name' => 'create banks', 'description' => 'Bancos', 'show' => true],
            ['name' => 'edit banks', 'description' => 'Bancos', 'show' => true],
            ['name' => 'view bank accounts', 'description' => 'Cta. bancarias', 'show' => true],
            ['name' => 'create bank accounts', 'description' => 'Cta. bancarias', 'show' => true],
            ['name' => 'edit bank accounts', 'description' => 'Cta. bancarias', 'show' => true],
            ['name' => 'view suppliers', 'description' => 'Proveedores', 'show' => true],
            ['name' => 'create suppliers', 'description' => 'Proveedores', 'show' => true],
            ['name' => 'edit suppliers', 'description' => 'Proveedores', 'show' => true],
            ['name' => 'view vouchers', 'description' => 'Comprobantes', 'show' => true],
            ['name' => 'create vouchers', 'description' => 'Comprobantes', 'show' => true],
            ['name' => 'edit vouchers', 'description' => 'Comprobantes', 'show' => true],
            ['name' => 'view income tax withholdings', 'description' => 'Imp. Gcias', 'show' => true],
            ['name' => 'create income tax withholdings', 'description' => 'Imp. Gcias', 'show' => true],
            ['name' => 'edit income tax withholdings', 'description' => 'Imp. Gcias', 'show' => true],
            ['name' => 'view social security tax withholdings', 'description' => 'Imp. Suss', 'show' => true],
            ['name' => 'create social security tax withholdings', 'description' => 'Imp. Suss', 'show' => true],
            ['name' => 'edit social security tax withholdings', 'description' => 'Imp. Suss', 'show' => true],
            ['name' => 'view vat tax withholdings', 'description' => 'Imp. IVA', 'show' => true],
            ['name' => 'create vat tax withholdings', 'description' => 'Imp. IVA', 'show' => true],
            ['name' => 'edit vat tax withholdings', 'description' => 'Imp. IVA', 'show' => true],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
