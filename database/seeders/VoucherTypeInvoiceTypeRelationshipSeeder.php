<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\VoucherTypeInvoiceTypeRelationship;
use Illuminate\Database\Seeder;

class VoucherTypeInvoiceTypeRelationshipSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $types = [
            /* INGRESOS */
            ['idVT' => 1, 'idIT' => 2],
            ['idVT' => 1, 'idIT' => 3],
            /* EGRESOS */
            ['idVT' => 2, 'idIT' => 1],
            ['idVT' => 2, 'idIT' => 2],
            ['idVT' => 2, 'idIT' => 4],

        ];

        foreach ($types as $type) {
            VoucherTypeInvoiceTypeRelationship::create($type);
        }
    }
}
