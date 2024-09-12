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
            ['idSupplier' => 23, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 3, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 2, 'invoiceNumber' => 14390, 'invoiceDate' => '2024-08-01', 'invoiceDueDate' => '2024-08-01', 'idPC' => 1, 'notes' => 'COMPRA DE INSUMOS INFORMÁTICOS PARA EL SECTOR ADMINISTRATIVO', 'totalAmount' => 255812.50,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['idSupplier' => 23, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 3, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 2, 'invoiceNumber' => 14399, 'invoiceDate' => '2024-08-03', 'invoiceDueDate' => '2024-08-03', 'idPC' => 2, 'notes' => 'COMPRA DE INSUMOS INFORMÁTICOS PARA EL SECTOR CONTABLE', 'totalAmount' => 1285400.00,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['idSupplier' => 23, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 3, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 2, 'invoiceNumber' => 14425, 'invoiceDate' => '2024-08-07', 'invoiceDueDate' => '2024-08-07', 'idPC' => 2, 'notes' => 'COMPRA DE INSUMOS INFORMÁTICOS PARA GERENCIA', 'totalAmount' => 3173450.00,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['idSupplier' => 23, 'idType' => 1, 'idSubtype' => 3, 'idExpense' => 3, 'idIT' => 3, 'idITCode' => 2, 'pointOfNumber' => 1, 'invoiceNumber' => 558, 'invoiceDate' => '2024-08-16', 'invoiceDueDate' => '2024-08-16', 'idPC' => 1, 'notes' => 'DEVOLUCIÓN POR FALTA DE STOCK', 'totalAmount' => 242000.00,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],

            ['idSupplier' => 6, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 9, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 5, 'invoiceNumber' => 3789754, 'invoiceDate' => '2024-08-26', 'invoiceDueDate' => '2024-09-02', 'idPC' => 1, 'notes' => NULL, 'totalAmount' => 947713.27,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['idSupplier' => 6, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 9, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 5, 'invoiceNumber' => 3789755, 'invoiceDate' => '2024-08-26', 'invoiceDueDate' => '2024-09-02', 'idPC' => 1, 'notes' => 'SECRETARÍA CENTRAL', 'totalAmount' => 816542.24,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],

            ['idSupplier' => 7, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 18, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 3102, 'invoiceNumber' => 903957, 'invoiceDate' => '2024-08-16', 'invoiceDueDate' => '2024-08-23', 'idPC' => 1, 'notes' => 'LINEAS FIJAS', 'totalAmount' => 150134.38,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['idSupplier' => 7, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 18, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 3102, 'invoiceNumber' => 942177, 'invoiceDate' => '2024-08-16', 'invoiceDueDate' => '2024-08-23', 'idPC' => 1, 'notes' => 'LINEAS FIJAS', 'totalAmount' => 32473.98,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['idSupplier' => 7, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 9, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 3093, 'invoiceNumber' => 9254, 'invoiceDate' => '2024-08-13', 'invoiceDueDate' => '2024-08-20', 'idPC' => 1, 'notes' => NULL, 'totalAmount' => 45980.00,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],

            ['idSupplier' => 8, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 2, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 3051, 'invoiceNumber' => 31304750, 'invoiceDate' => '2024-08-08', 'invoiceDueDate' => '2024-08-15', 'idPC' => 1, 'notes' => 'LINEAS CELULARES', 'totalAmount' => 75764.17,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['idSupplier' => 8, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 2, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 3061, 'invoiceNumber' => 03143352, 'invoiceDate' => '2024-08-08', 'invoiceDueDate' => '2024-08-15', 'idPC' => 1, 'notes' => 'LINEAS CELULARES', 'totalAmount' => 563752.75,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],

            ['idSupplier' => 10, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 12, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 501, 'invoiceNumber' => 30276540, 'invoiceDate' => '2024-08-27', 'invoiceDueDate' => '2024-09-03', 'idPC' => 1, 'notes' => NULL, 'totalAmount' => 8383.38,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['idSupplier' => 10, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 12, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 501, 'invoiceNumber' => 30334266, 'invoiceDate' => '2024-08-27', 'invoiceDueDate' => '2024-09-03', 'idPC' => 1, 'notes' => NULL, 'totalAmount' => 266922.7,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
            ['idSupplier' => 10, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 12, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 501, 'invoiceNumber' => 30330833, 'invoiceDate' => '2024-08-27', 'invoiceDueDate' => '2024-09-03', 'idPC' => 1, 'notes' => NULL, 'totalAmount' => 282436.88,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],

            ['idSupplier' => 4, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 14, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 4, 'invoiceNumber' => 15656, 'invoiceDate' => '2024-09-02', 'invoiceDueDate' => '2024-09-02', 'idPC' => 1, 'notes' => 'CONTROL DE AUSENTISMO DE EMPLEADOS', 'totalAmount' => 97870.85,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],

            ['idSupplier' => 20, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 4, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 1, 'invoiceNumber' => 269936, 'invoiceDate' => '2024-08-01', 'invoiceDueDate' => '2024-08-01', 'idPC' => 1, 'notes' => 'RESPUESTA INTIMACIÓN DE LA EMPRESA', 'totalAmount' => 8700.00,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],

            ['idSupplier' => 2, 'idType' => 2, 'idSubtype' => 4, 'idExpense' => 20, 'idIT' => 1, 'idITCode' => 2, 'pointOfNumber' => 2, 'invoiceNumber' => 965, 'invoiceDate' => '2024-08-24', 'invoiceDueDate' => '2024-08-31', 'idPC' => 2, 'notes' => NULL, 'totalAmount' => 15412773.74,  'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null,  'status' => true],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::create($voucher);
        }
    }
}
