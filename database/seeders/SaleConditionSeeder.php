<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\SaleCondition;
use Illuminate\Database\Seeder;

class SaleConditionSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $saleConditions = [
            ['name' => 'CONTADO'],
            ['name' => 'CUENTA CORRIENTE'],
        ];

        SaleCondition::withoutTimestamps(function () use ($saleConditions) {
            foreach ($saleConditions as $saleCondition) {
                SaleCondition::create($saleCondition);
            }
        });
    }
}
