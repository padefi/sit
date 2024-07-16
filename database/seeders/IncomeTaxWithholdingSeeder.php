<?php

namespace Database\Seeders;

use App\Models\Treasury\Taxes\IncomeTaxWithholding;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IncomeTaxWithholdingSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $incomeTaxWithholdings = [
            ['idCat' => 2, 'rate' => 6.00, 'minAmount' => 7870.00, 'fixedAmount' => 0.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 3, 'rate' => 6.00, 'minAmount' => 11200.00, 'fixedAmount' => 0.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 4, 'rate' => 6.00, 'minAmount' => 11200.00, 'fixedAmount' => 0.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 5, 'rate' => 2.00, 'minAmount' => 224000.00, 'fixedAmount' => 0.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 6, 'rate' => 2.00, 'minAmount' => 67170.00, 'fixedAmount' => 0.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 9, 'rate' => 1.00, 'minAmount' => 0.00, 'fixedAmount' => 0.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 10, 'rate' => 2.00, 'minAmount' => 224000.00, 'fixedAmount' => 0.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
        ];

        foreach ($incomeTaxWithholdings as $incomeTaxWithholding) {
            IncomeTaxWithholding::create($incomeTaxWithholding);
        }
    }
}
