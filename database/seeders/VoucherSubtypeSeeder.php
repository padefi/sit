<?php

namespace Database\Seeders;

use App\Models\Treasury\VoucherSubtype;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VoucherSubtypeSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $voucherSubtypes = [
            ['name' => 'AJUSTE DE CAJA', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'ANTICIPO A PROVEEDORES', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'DEVOLUCION PROVEEDORES', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'PAGO A PROVEEDORES', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'RETIRO DE FONDOS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'RETENCION GCIAS.', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'RETENCION SUSS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'SUELDOS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'VALORES A DEPOSITAR', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
        ];

        foreach ($voucherSubtypes as $subtype) {
            VoucherSubtype::create($subtype);
        }
    }
}