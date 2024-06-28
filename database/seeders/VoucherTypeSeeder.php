<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\VoucherType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VoucherTypeSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $voucherTypes = [
            ['name' => 'INGRESOS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['name' => 'EGRESOS', 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null, 'status' => true],
        ];

        foreach ($voucherTypes as $type) {
            VoucherType::create($type);
        }
    }
}
