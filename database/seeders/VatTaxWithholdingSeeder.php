<?php

namespace Database\Seeders;

use App\Models\Treasury\Taxes\VatTaxWithholding;
use Illuminate\Database\Seeder;

class VatTaxWithholdingSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $vatTaxWithholdings = [
            ['idCat' => 6, 'rate' => 8.68, 'minAmount' => 0.00, 'fixedAmount' => 0.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
        ];

        foreach ($vatTaxWithholdings as $vatTaxWithholding) {
            VatTaxWithholding::create($vatTaxWithholding);
        }
    }
}
