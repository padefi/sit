<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\VoucherToTreasury;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VoucherToTreasurySeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $vouchersToTreasury = [
            ['idVoucher' => 1, 'idTV' => 1, 'amount' => 255812.5, 'idUserSent' => 1, 'related_at' => now()],
            ['idVoucher' => 2, 'idTV' => 2, 'amount' => 500000.00, 'idUserSent' => 1, 'related_at' => now()],
            ['idVoucher' => 3, 'idTV' => 2, 'amount' => 1000000.00, 'idUserSent' => 1, 'related_at' => now()],
            ['idVoucher' => 4, 'idTV' => 2, 'amount' => 242000.00, 'idUserSent' => 1, 'related_at' => now()],
            ['idVoucher' => 7, 'idTV' => 3, 'amount' => 150134.38, 'idUserSent' => 1, 'related_at' => now()],
            ['idVoucher' => 10, 'idTV' => 4, 'amount' => 75764.17, 'idUserSent' => 1, 'related_at' => now()],
            ['idVoucher' => 11, 'idTV' => 4, 'amount' => 563752.75, 'idUserSent' => 1, 'related_at' => now()],
            ['idVoucher' => 15, 'idTV' => 5, 'amount' => 97870.85, 'idUserSent' => 1, 'related_at' => now()],
            ['idVoucher' => 13, 'idTV' => 6, 'amount' => 266922.70, 'idUserSent' => 1, 'related_at' => now()],
            ['idVoucher' => 14, 'idTV' => 6, 'amount' => 282436.88, 'idUserSent' => 1, 'related_at' => now()],
            ['idVoucher' => 5, 'idTV' => 7, 'amount' => 947713.27, 'idUserSent' => 1, 'related_at' => now()],
            ['idVoucher' => 6, 'idTV' => 7, 'amount' => 816542.24, 'idUserSent' => 1, 'related_at' => now()],
            ['idVoucher' => 17, 'idTV' => 8, 'amount' => 10000000, 'idUserSent' => 1, 'related_at' => now()],
        ];

        foreach ($vouchersToTreasury as $voucherToTreasury) {
            VoucherToTreasury::create($voucherToTreasury);
        }
    }
}
