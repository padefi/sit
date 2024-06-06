<?php

namespace Database\Seeders;

use App\Models\Treasury\VoucherExpense;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VoucherExpenseSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $voucherExpenses = [
            ['name' => 'AGUA', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['name' => 'CELULARES', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'COMPUTACION', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'CORREO', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'FARMACIA', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'FOTOCOPIAS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'GAS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'HONORARIOS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'INTERNET', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'LIBRERIA', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'LIMPIEZA', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'LUZ', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'MANTENIMIENTO', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'MEDICOS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'MUEBLES Y UTILES', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'SEGUROS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'TELEFONOS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'VARIOS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
            ['name' => 'VIATICOS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
        ];

        foreach ($voucherExpenses as $expense) {
            VoucherExpense::create($expense);
        }
    }
}
