<?php

namespace Database\Seeders;

use App\Models\Treasury\Taxes\TaxCondition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxConditionSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $taxCondition = [
            ['name' => 'IVA RESPONSABLE INSCRIPTO'],
            ['name' => 'IVA SUJETO EXENTO'],
            ['name' => 'CONSUMIDOR FINAL'],
            ['name' => 'RESPONSABLE MONOTRIBUTO'],
            ['name' => 'SUJETO NO CATEGORIZADO'],
            ['name' => 'IVA NO ALCANZADO'],
        ];

        TaxCondition::withoutTimestamps(function () use ($taxCondition) {
            foreach ($taxCondition as $condition) {
                TaxCondition::create($condition);
            }
        });
    }
}
