<?php

namespace Database\Seeders;

use App\Models\Treasury\TreasuryVoucher\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $paymentMethods = [
            ['name' => 'TRANSFERENCIA'],
            ['name' => 'CHEQUE'],
            ['name' => 'DEPÓSITO'],
            ['name' => 'EFECTIVO'],
        ];

        foreach ($paymentMethods as $paymentMethod) {
            PaymentMethod::create($paymentMethod);
        }
    }
}
