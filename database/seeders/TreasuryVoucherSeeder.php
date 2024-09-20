<?php

namespace Database\Seeders;

use App\Models\Treasury\TreasuryVoucher\TreasuryVoucher;
use Illuminate\Database\Seeder;

class TreasuryVoucherSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $treasuryVouchers = [
            ['idType' => 2, 'idSupplier' => 23, 'idPM' => NULL, 'idBA' => NULL, 'number' => NULL, 'idVS' => 1, 'amount' => 255812.5, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 255812.5, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['idType' => 2, 'idSupplier' => 23, 'idPM' => NULL, 'idBA' => NULL, 'number' => NULL, 'idVS' => 1, 'amount' => 1258000.00, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 1258000.00, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['idType' => 2, 'idSupplier' => 7, 'idPM' => NULL, 'idBA' => NULL, 'number' => NULL, 'idVS' => 1, 'amount' => 150134.38, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 150134.38, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['idType' => 2, 'idSupplier' => 8, 'idPM' => NULL, 'idBA' => NULL, 'number' => NULL, 'idVS' => 1, 'amount' => 639516.92, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 639516.92, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['idType' => 2, 'idSupplier' => 4, 'idPM' => NULL, 'idBA' => NULL, 'number' => NULL, 'idVS' => 1, 'amount' => 97870.85, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 97870.85, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['idType' => 2, 'idSupplier' => 10, 'idPM' => NULL, 'idBA' => NULL, 'number' => NULL, 'idVS' => 1, 'amount' => 549359.58, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 549359.58, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['idType' => 2, 'idSupplier' => 6, 'idPM' => NULL, 'idBA' => NULL, 'number' => NULL, 'idVS' => 1, 'amount' => 1764255.51, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 1764255.51, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['idType' => 2, 'idSupplier' => 2, 'idPM' => NULL, 'idBA' => NULL, 'number' => NULL, 'idVS' => 1, 'amount' => 10000000.00, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 10000000.00, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['idType' => 2, 'idSupplier' => 23, 'idPM' => NULL, 'idBA' => NULL, 'number' => NULL, 'idVS' => 1, 'amount' => 500000.00, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 500000.00, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
        ];

        foreach ($treasuryVouchers as $treasuryVoucher) {
            TreasuryVoucher::create($treasuryVoucher);
        }
    }
}
