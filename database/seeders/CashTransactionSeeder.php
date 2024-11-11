<?php

namespace Database\Seeders;

use App\Models\Treasury\TreasuryVoucher\CashTransaction as TreasuryVoucherCashTransaction;
use Illuminate\Database\Seeder;

class CashTransactionSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $cashTransactions = [
            ['idTV' => 11, 'amount' => 1000000, 'idUserConfirmed' => 1, 'idUserVoided' => null, 'confirmed_at' => now(), 'voided_at' => null,  'status' => true],
        ];

        foreach ($cashTransactions as $cashTransaction) {
            TreasuryVoucherCashTransaction::create($cashTransaction);
        }
    }
}
