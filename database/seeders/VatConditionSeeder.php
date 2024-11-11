<?php

namespace Database\Seeders;

use App\Models\Treasury\Taxes\VatCondition;
use Illuminate\Database\Seeder;

class VatConditionSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $vatCondition = [
            ['name' => 'IVA RESPONSABLE INSCRIPTO'],
            ['name' => 'IVA SUJETO EXENTO'],
            ['name' => 'CONSUMIDOR FINAL'],
            ['name' => 'RESPONSABLE MONOTRIBUTO'],
            ['name' => 'SUJETO NO CATEGORIZADO'],
            ['name' => 'IVA NO ALCANZADO'],
        ];

        VatCondition::withoutTimestamps(function () use ($vatCondition) {
            foreach ($vatCondition as $condition) {
                VatCondition::create($condition);
            }
        });
    }
}
