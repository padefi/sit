<?php

namespace Database\Seeders;

use App\Models\Treasury\Taxes\TaxType;
use Illuminate\Database\Seeder;

class TaxTypeSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $taxTypes = [
            ['name' => 'GANANCIAS'],
            ['name' => 'SUSS'],
            ['name' => 'I.V.A.'],
        ];

        TaxType::withoutTimestamps(function () use ($taxTypes) {
            foreach ($taxTypes as $taxType) {
                TaxType::create($taxType);
            }
        });
    }
}
