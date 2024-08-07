<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\InvoiceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoiceTypeSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $invoiceTypes = [
            ['name' => 'FACTURA'],
            ['name' => 'RECIBO'],
            ['name' => 'NOTA DE CREDITO'],
            ['name' => 'NOTA DE DEBITO'],
        ];

        InvoiceType::withoutTimestamps(function () use ($invoiceTypes) {
            foreach ($invoiceTypes as $invoiceType) {
                InvoiceType::create($invoiceType);
            }
        });
    }
}
