<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\TreasuryVoucher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreasuryVoucherSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $treasuryVouchers = [
            ['idType' => 2, 'idSupplier' => 22, 'idPM' => NULL, 'idBA' => NULL, 'idVS' => 1, 'amount' => 255812.5, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 255812.5, 'idUserCreated' => 1, 'idUserUpdated' => 1, 'idUserConfirmed' => 1, 'idUserVoided' => 1, 'created_at' => now(), 'updated_at' => null, 'confirmed_at' => null, 'voided_at' => null],
            ['idType' => 2, 'idSupplier' => 22, 'idPM' => NULL, 'idBA' => NULL, 'idVS' => 1, 'amount' => 1258000.00, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 1258000.00, 'idUserCreated' => 1, 'idUserUpdated' => 1, 'idUserConfirmed' => 1, 'idUserVoided' => 1, 'created_at' => now(), 'updated_at' => null, 'confirmed_at' => null, 'voided_at' => null],
            ['idType' => 2, 'idSupplier' => 6, 'idPM' => NULL, 'idBA' => NULL, 'idVS' => 1, 'amount' => 150134.38, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 150134.38, 'idUserCreated' => 1, 'idUserUpdated' => 1, 'idUserConfirmed' => 1, 'idUserVoided' => 1, 'created_at' => now(), 'updated_at' => null, 'confirmed_at' => null, 'voided_at' => null],
            ['idType' => 2, 'idSupplier' => 7, 'idPM' => NULL, 'idBA' => NULL, 'idVS' => 1, 'amount' => 639516.92, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 639516.92, 'idUserCreated' => 1, 'idUserUpdated' => 1, 'idUserConfirmed' => 1, 'idUserVoided' => 1, 'created_at' => now(), 'updated_at' => null, 'confirmed_at' => null, 'voided_at' => null],
            ['idType' => 2, 'idSupplier' => 3, 'idPM' => NULL, 'idBA' => NULL, 'idVS' => 1, 'amount' => 97870.85, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 97870.85, 'idUserCreated' => 1, 'idUserUpdated' => 1, 'idUserConfirmed' => 1, 'idUserVoided' => 1, 'created_at' => now(), 'updated_at' => null, 'confirmed_at' => null, 'voided_at' => null],
            ['idType' => 2, 'idSupplier' => 9, 'idPM' => NULL, 'idBA' => NULL, 'idVS' => 1, 'amount' => 549359.58, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 549359.58, 'idUserCreated' => 1, 'idUserUpdated' => 1, 'idUserConfirmed' => 1, 'idUserVoided' => 1, 'created_at' => now(), 'updated_at' => null, 'confirmed_at' => null, 'voided_at' => null],
            ['idType' => 2, 'idSupplier' => 5, 'idPM' => NULL, 'idBA' => NULL, 'idVS' => 1, 'amount' => 1764255.51, 'incomeTaxAmount' => 0, 'socialTaxAmount' => 0, 'vatTaxAmount' => 0, 'totalAmount' => 1764255.51, 'idUserCreated' => 1, 'idUserUpdated' => 1, 'idUserConfirmed' => 1, 'idUserVoided' => 1, 'created_at' => now(), 'updated_at' => null, 'confirmed_at' => null, 'voided_at' => null],
        ];

        foreach ($treasuryVouchers as $treasuryVoucher) {
            TreasuryVoucher::create($treasuryVoucher);
        }
    }
}