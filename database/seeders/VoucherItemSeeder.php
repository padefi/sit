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
            ['idVoucher' => 1, 'description' => "MONITOR 24''", 'amount' => 200000, 'idVat' => 3, 'subtotalAmount' => 242000],
            ['idVoucher' => 1, 'description' => 'TECLADO', 'amount' => 10000, 'idVat' => 2, 'subtotalAmount' => 11050],
            ['idVoucher' => 1, 'description' => 'MOUSE', 'amount' => 2500, 'idVat' => 2, 'subtotalAmount' => 2762.5],
        ];

        foreach ($voucherItems as $item) {
            VoucherItem::create($item);
        }
    }
}
