<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\InvoiceTypeInvoiceTypeCodeRelationship;
use Illuminate\Database\Seeder;

class InvoiceTypeInvoiceTypeCodeRelationshipSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $types = [
            /* FACTURA */
            ['idIT' => 1, 'idITCode' => 1],
            ['idIT' => 1, 'idITCode' => 2],
            ['idIT' => 1, 'idITCode' => 3],
            ['idIT' => 1, 'idITCode' => 5],
            /* RECIBO */
            ['idIT' => 2, 'idITCode' => 4],
            /* NOTA DE CREDITO */
            ['idIT' => 3, 'idITCode' => 1],
            ['idIT' => 3, 'idITCode' => 2],
            ['idIT' => 3, 'idITCode' => 3],
            /* NOTA DE DEBITO */
            ['idIT' => 4, 'idITCode' => 1],
            ['idIT' => 4, 'idITCode' => 2],
            ['idIT' => 4, 'idITCode' => 3],

        ];

        foreach ($types as $type) {
            InvoiceTypeInvoiceTypeCodeRelationship::create($type);
        }
    }
}
