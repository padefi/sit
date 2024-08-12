<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\TreasuryVoucherStatus;
use Illuminate\Database\Seeder;

class TreasuryVoucherStatusSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $voucherStatuses = [
            ['name' => 'PENDIENTE'],
            ['name' => 'CONFIRMADO'],
            ['name' => 'ANULADO'],
        ];

        foreach ($voucherStatuses as $voucherStatus) {
            TreasuryVoucherStatus::create($voucherStatus);
        }
    }
}
