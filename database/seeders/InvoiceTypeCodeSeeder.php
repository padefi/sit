<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\InvoiceTypeCode;
use Illuminate\Database\Seeder;

class InvoiceTypeCodeSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $invoiceTypeCodes = [
            ['name' => 'A'],
            ['name' => 'B'],
            ['name' => 'C'],
            ['name' => 'X'],
            ['name' => 'TICKET'],
        ];

        InvoiceTypeCode::withoutTimestamps(function () use ($invoiceTypeCodes) {
            foreach ($invoiceTypeCodes as $invoiceTypeCode) {
                InvoiceTypeCode::create($invoiceTypeCode);
            }
        });
    }
}
