<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /* $permissions = [
            ['name' => 'view users'],
            ['name' => 'create users'],
            ['name' => 'edit users'],
            ['name' => 'permission users'],
            ['name' => 'view voucher types'],
            ['name' => 'create voucher types'],
            ['name' => 'edit voucher types'],
            ['name' => 'relationship voucher types'],
            ['name' => 'view voucher subtypes'],
            ['name' => 'create voucher subtypes'],
            ['name' => 'edit voucher subtypes'],
            ['name' => 'relationship voucher subtypes'],
            ['name' => 'view voucher expenses'],
            ['name' => 'create voucher expenses'],
            ['name' => 'edit voucher expenses'],
            ['name' => 'view banks'],
            ['name' => 'create banks'],
            ['name' => 'edit banks'],
            ['name' => 'view bank accounts'],
            ['name' => 'create bank accounts'],
            ['name' => 'edit bank accounts'],
            ['name' => 'view providers'],
            ['name' => 'create providers'],
            ['name' => 'edit providers'],
            ['name' => 'view vouchers'],
            ['name' => 'create vouchers'],
            ['name' => 'edit vouchers'],
            ['name' => 'void vouchers'],
            ['name' => 'view income tax category'],
            ['name' => 'create income tax category'],
            ['name' => 'edit income tax category'],
            ['name' => 'view income tax withholdings'],
            ['name' => 'create income tax withholdings'],
            ['name' => 'edit income tax withholdings'],
            ['name' => 'view income tax withholdings scale'],
            ['name' => 'create income tax withholdings scale'],
            ['name' => 'edit income tax withholdings scale'],
            ['name' => 'view social security category'],
            ['name' => 'create social security category'],
            ['name' => 'edit social security category'],
            ['name' => 'view social security withholdings'],
            ['name' => 'create social security withholdings'],
            ['name' => 'edit social security withholdings'],
            ['name' => 'view social security withholdings scale'],
            ['name' => 'create social security withholdings scale'],
            ['name' => 'edit social security withholdings scale'],            
        ]; */

        $permissions = [
            ['name' => 'view users', 'description' => 'Usuarios', 'show' => false],
            ['name' => 'create users', 'description' => 'Usuarios', 'show' => false],
            ['name' => 'edit users', 'description' => 'Usuarios', 'show' => false],
            ['name' => 'permission users', 'description' => 'Usuarios', 'show' => false],
            ['name' => 'view voucher types', 'description' => 'Tipo', 'show' => false],
            ['name' => 'create voucher types', 'description' => 'Tipo', 'show' => false],
            ['name' => 'edit voucher types', 'description' => 'Tipo', 'show' => false],
            ['name' => 'relationship voucher types', 'description' => 'Tipo', 'show' => false],
            ['name' => 'view voucher subtypes', 'description' => 'Subipo', 'show' => true],
            ['name' => 'create voucher subtypes', 'description' => 'Subipo', 'show' => true],
            ['name' => 'edit voucher subtypes', 'description' => 'Subipo', 'show' => true],
            ['name' => 'relationship voucher subtypes', 'description' => 'Subipo', 'show' => false],
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
            ['name' => 'view social security withholdings', 'description' => 'Imp. Suss', 'show' => true],
            ['name' => 'create social security withholdings', 'description' => 'Imp. Suss', 'show' => true],
            ['name' => 'edit social security withholdings', 'description' => 'Imp. Suss', 'show' => true],           
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
