<?php

namespace Database\Seeders;

use App\Models\Treasury\Taxes\VatRate;
use Illuminate\Database\Seeder;

class VatRateSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $rates = [
            ['rate' => 0],
            ['rate' => 10.5],
            ['rate' => 21],
        ];

        VatRate::withoutTimestamps(function () use ($rates) {
            foreach ($rates as $rate) {
                VatRate::create($rate);
            }
        });
    }
}
