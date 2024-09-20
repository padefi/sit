<?php

namespace Database\Seeders;

use App\Models\Treasury\TreasuryVoucher\TreasuryCustomVoucher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreasuryCustomVoucherSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $treasuryCustomVouchers = [
            ['idTV' => 9, 'idSupplier' => 23, 'idType' => 2, 'idSubtype' => 2, 'idExpense' => 3, 'amount' => 500000.00, 'notes' => 'SEÑA PARA SERVIDOR HPE PROLIANT DL380 GEN10', 'voucherDate' => now(), 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
        ];

        foreach ($treasuryCustomVouchers as $treasuryCustomVoucher) {
            TreasuryCustomVoucher::create($treasuryCustomVoucher);
        }
    }
}
