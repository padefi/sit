<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $vouchers = [
            ['idSupplier' => 21, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 3, 'idIT' => 1, 'idITCode' => 1, 'pointOfNumber' => 1, 'invoiceNumber' => 1, 'invoiceDate' => '2024-08-01', 'invoiceDueDate' => '2024-08-08', 'idPC' => 1, 'notes' => 'COMPRA DE INSUMOS INFORMÁTICOS PARA EL SECTOR ADMINISTRATIVO', 'totalAmount' => 255812.50,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['idSupplier' => 21, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 3, 'idIT' => 1, 'idITCode' => 1, 'pointOfNumber' => 1, 'invoiceNumber' => 2, 'invoiceDate' => '2024-08-03', 'invoiceDueDate' => '2024-08-15', 'idPC' => 2, 'notes' => 'COMPRA DE INSUMOS INFORMÁTICOS PARA EL SECTOR CONTABLE', 'totalAmount' => 1285400.00,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['idSupplier' => 21, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 3, 'idIT' => 1, 'idITCode' => 1, 'pointOfNumber' => 1, 'invoiceNumber' => 3, 'invoiceDate' => '2024-08-07', 'invoiceDueDate' => '2024-08-21', 'idPC' => 2, 'notes' => 'COMPRA DE INSUMOS INFORMÁTICOS PARA GERENCIA', 'totalAmount' => 3173450.00,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['idSupplier' => 21, 'idType' => 1, 'idSubtype' => 3, 'idExpense' => 3, 'idIT' => 3, 'idITCode' => 1, 'pointOfNumber' => 1, 'invoiceNumber' => 1, 'invoiceDate' => '2024-08-16', 'invoiceDueDate' => '2024-08-16', 'idPC' => 1, 'notes' => 'DEVOLUCIÓN POR FALTA DE STOCK', 'totalAmount' => 242000.00,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::create($voucher);
        }
    }
}
