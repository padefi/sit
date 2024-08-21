<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\VoucherItem;
use Illuminate\Database\Seeder;

class VoucherItemSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $voucherItems = [
            ['idVoucher' => 1, 'description' => "MONITOR 24''", 'amount' => 200000.00, 'idVat' => 3, 'subtotalAmount' => 242000.00],
            ['idVoucher' => 1, 'description' => 'TECLADO', 'amount' => 10000.00, 'idVat' => 2, 'subtotalAmount' => 11050.00],
            ['idVoucher' => 1, 'description' => 'MOUSE', 'amount' => 2500.00, 'idVat' => 2, 'subtotalAmount' => 2762.5],

            ['idVoucher' => 2, 'description' => "IMPRESORA HP LASERJET", 'amount' => 350000.00, 'idVat' => 3, 'subtotalAmount' => 423500.00],
            ['idVoucher' => 2, 'description' => 'NOTEBOOK HP i3', 'amount' => 780000.00, 'idVat' => 2, 'subtotalAmount' => 861900.00],

            ['idVoucher' => 3, 'description' => "NOTEBOOK HP i5", 'amount' => 1500000.00, 'idVat' => 2, 'subtotalAmount' => 1657500.00],
            ['idVoucher' => 3, 'description' => 'NOTEBOOK ASUS RYZEN 5', 'amount' => 1350000.00, 'idVat' => 2, 'subtotalAmount' => 1491750.00],
            ['idVoucher' => 3, 'description' => 'PENDRIVE 32GB', 'amount' => 20000.00, 'idVat' => 3, 'subtotalAmount' => 24200.00],

            ['idVoucher' => 4, 'description' => "MONITOR 24''", 'amount' => 200000.00, 'idVat' => 3, 'subtotalAmount' => 242000.00],
        ];

        foreach ($voucherItems as $item) {
            VoucherItem::create($item);
        }
    }
}
