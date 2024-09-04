<?php

namespace Database\Seeders;

use App\Models\Treasury\TreasuryVoucher\PayCondition;
use Illuminate\Database\Seeder;

class PayConditionSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $payConditions = [
            ['name' => 'CONTADO'],
            ['name' => 'CUENTA CORRIENTE'],
        ];

        PayCondition::withoutTimestamps(function () use ($payConditions) {
            foreach ($payConditions as $payCondition) {
                PayCondition::create($payCondition);
            }
        });
    }
}
