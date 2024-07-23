<?php

namespace Database\Seeders;

use App\Models\Treasury\Taxes\IncomeTaxWithholdingScale;
use Illuminate\Database\Seeder;

class IncomeTaxWithholdingScaleSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $incomeTaxWithholdingScales = [
            // Comisiones
            ['idCat' => 7, 'rate' => 5.00, 'minAmount' => 0.00, 'maxAmount' => 8000.00, 'fixedAmount' => 0.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 7, 'rate' => 9.00, 'minAmount' => 8000.00, 'maxAmount' => 16000.00, 'fixedAmount' => 400.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 7, 'rate' => 12.00, 'minAmount' => 16000.00, 'maxAmount' => 24000.00, 'fixedAmount' => 1120.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 7, 'rate' => 15.00, 'minAmount' => 24000.00, 'maxAmount' => 32000.00, 'fixedAmount' => 2080.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 7, 'rate' => 19.00, 'minAmount' => 32000.00, 'maxAmount' => 48000.00, 'fixedAmount' => 3280.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 7, 'rate' => 23.00, 'minAmount' => 48000.00, 'maxAmount' => 64000.00, 'fixedAmount' => 6320.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 7, 'rate' => 27.00, 'minAmount' => 64000.00, 'maxAmount' => 96000.00, 'fixedAmount' => 10000.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 7, 'rate' => 31.00, 'minAmount' => 96000.00, 'maxAmount' => 99999999.00, 'fixedAmount' => 18640.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],

            // Profesiones liberales
            ['idCat' => 8, 'rate' => 5.00, 'minAmount' => 0.00, 'maxAmount' => 71000.00, 'fixedAmount' => 0.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 8, 'rate' => 9.00, 'minAmount' => 71000.00, 'maxAmount' => 142000.00, 'fixedAmount' => 3550.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 8, 'rate' => 12.00, 'minAmount' => 142000.00, 'maxAmount' => 213000.00, 'fixedAmount' => 9940.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 8, 'rate' => 15.00, 'minAmount' => 213000.00, 'maxAmount' => 284000.00, 'fixedAmount' => 18460.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 8, 'rate' => 19.00, 'minAmount' => 284000.00, 'maxAmount' => 426000.00, 'fixedAmount' => 29110.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 8, 'rate' => 23.00, 'minAmount' => 426000.00, 'maxAmount' => 568000.00, 'fixedAmount' => 56090.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 8, 'rate' => 27.00, 'minAmount' => 568000.00, 'maxAmount' => 852000.00, 'fixedAmount' => 88750.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
            ['idCat' => 8, 'rate' => 31.00, 'minAmount' => 852000.00, 'maxAmount' => 99999999.00, 'fixedAmount' => 165430.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
        ];

        foreach ($incomeTaxWithholdingScales as $incomeTaxWithholdingScale) {
            IncomeTaxWithholdingScale::create($incomeTaxWithholdingScale);
        }
    }
}
