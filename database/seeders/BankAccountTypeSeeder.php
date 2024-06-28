<?php

namespace Database\Seeders;

use App\Models\Treasury\Bank\BankAccountType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankAccountTypeSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $bankAccountTypes = [
            ['name' => 'C.A.'],
            ['name' => 'C.C.'],
        ];

        BankAccountType::withoutTimestamps(function () use ($bankAccountTypes) {
            foreach ($bankAccountTypes as $bankAccountType) {
                BankAccountType::create($bankAccountType);
            }
        });
    }
}
