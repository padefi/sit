<?php

namespace Database\Seeders;

use App\Models\Treasury\Taxes\SocialSecurityTaxWithholding;
use Illuminate\Database\Seeder;

class SocialSecurityTaxWithholdingSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $socialSecurityTaxWithholdings = [
            ['idCat' => 6, 'rate' => 6.00, 'minAmount' => 0.00, 'fixedAmount' => 0.00, 'startAt' => '2020-01-01', 'endAt' => '2100-12-31', 'idUserCreated' => 1, 'updated_at' => null],
        ];

        foreach ($socialSecurityTaxWithholdings as $socialSecurityTaxWithholding) {
            SocialSecurityTaxWithholding::create($socialSecurityTaxWithholding);
        }
    }
}
