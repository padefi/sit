<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\VoucherItem;
use Illuminate\Database\Seeder;

class VoucherItemSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $voucherItems = [
            ['idVoucher' => 1, 'description' => "MONITOR 24''", 'amount' => 200000.00, 'idVat' => 3, 'subtotalAmount' => 242000.00],
            ['idVoucher' => 1, 'description' => 'TECLADO', 'amount' => 10000.00, 'idVat' => 2, 'subtotalAmount' => 11050.00],
            ['idVoucher' => 1, 'description' => 'MOUSE', 'amount' => 2500.00, 'idVat' => 2, 'subtotalAmount' => 2762.5],

            ['idVoucher' => 2, 'description' => "IMPRESORA HP LASERJET", 'amount' => 350000.00, 'idVat' => 3, 'subtotalAmount' => 423500.00],
            ['idVoucher' => 2, 'description' => 'NOTEBOOK HP i3', 'amount' => 780000.00, 'idVat' => 2, 'subtotalAmount' => 861900.00],

            ['idVoucher' => 3, 'description' => "NOTEBOOK HP i5", 'amount' => 1500000.00, 'idVat' => 2, 'subtotalAmount' => 1657500.00],
            ['idVoucher' => 3, 'description' => 'NOTEBOOK ASUS RYZEN 5', 'amount' => 1350000.00, 'idVat' => 2, 'subtotalAmount' => 1491750.00],
            ['idVoucher' => 3, 'description' => 'PENDRIVE 32GB', 'amount' => 20000.00, 'idVat' => 3, 'subtotalAmount' => 24200.00],

            ['idVoucher' => 4, 'description' => "MONITOR 24''", 'amount' => 200000.00, 'idVat' => 3, 'subtotalAmount' => 242000.00],
        
            ['idVoucher' => 5, 'description' => "ABONO INTERNET FULL", 'amount' => 947713.27, 'idVat' => 1, 'subtotalAmount' => 947713.27],

            ['idVoucher' => 6, 'description' => "ABONO INTERNET FULL", 'amount' => 816542.24, 'idVat' => 1, 'subtotalAmount' => 816542.24],

            ['idVoucher' => 7, 'description' => "LÍNEA 0975", 'amount' => 50044.79, 'idVat' => 1, 'subtotalAmount' => 50044.79],
            ['idVoucher' => 7, 'description' => "LÍNEA 0650", 'amount' => 50044.79, 'idVat' => 1, 'subtotalAmount' => 50044.79],
            ['idVoucher' => 7, 'description' => "LÍNEA 2186", 'amount' => 50044.80, 'idVat' => 1, 'subtotalAmount' => 50044.80],

            ['idVoucher' => 8, 'description' => "LÍNEA 6286", 'amount' => 16236.99, 'idVat' => 1, 'subtotalAmount' => 16236.99],
            ['idVoucher' => 8, 'description' => "LÍNEA 6276", 'amount' => 16236.99, 'idVat' => 1, 'subtotalAmount' => 16236.99],

            ['idVoucher' => 9, 'description' => "ABONO INTERNET FULL", 'amount' => 45980.00, 'idVat' => 1, 'subtotalAmount' => 45980.00],

            ['idVoucher' => 10, 'description' => "LINEA 8513", 'amount' => 75764.17, 'idVat' => 1, 'subtotalAmount' => 75764.17],

            ['idVoucher' => 11, 'description' => "LINEA 7940", 'amount' => 80536.10, 'idVat' => 1, 'subtotalAmount' => 80536.10],
            ['idVoucher' => 11, 'description' => "LINEA 6699", 'amount' => 80536.10, 'idVat' => 1, 'subtotalAmount' => 80536.10],
            ['idVoucher' => 11, 'description' => "LINEA 7871", 'amount' => 80536.11, 'idVat' => 1, 'subtotalAmount' => 80536.11],
            ['idVoucher' => 11, 'description' => "LINEA 3232", 'amount' => 80536.11, 'idVat' => 1, 'subtotalAmount' => 80536.11],
            ['idVoucher' => 11, 'description' => "LINEA 6751", 'amount' => 80536.11, 'idVat' => 1, 'subtotalAmount' => 80536.11],
            ['idVoucher' => 11, 'description' => "LINEA 3680", 'amount' => 80536.11, 'idVat' => 1, 'subtotalAmount' => 80536.11],
            ['idVoucher' => 11, 'description' => "LINEA 7166", 'amount' => 80536.11, 'idVat' => 1, 'subtotalAmount' => 80536.11],

            ['idVoucher' => 12, 'description' => "LUZ P.B.", 'amount' => 8383.38, 'idVat' => 1, 'subtotalAmount' => 8383.38],

            ['idVoucher' => 13, 'description' => "LUZ 3° PISO", 'amount' => 266922.7, 'idVat' => 1, 'subtotalAmount' => 266922.7],

            ['idVoucher' => 14, 'description' => "LUZ 4° PISO", 'amount' => 282436.88, 'idVat' => 1, 'subtotalAmount' => 282436.88],

            ['idVoucher' => 15, 'description' => "ABONO MENSUAL", 'amount' => 97870.85, 'idVat' => 1, 'subtotalAmount' => 97870.85],

            ['idVoucher' => 16, 'description' => "CARTA DOCUMENTO", 'amount' => 8700.00, 'idVat' => 1, 'subtotalAmount' => 8700.00],

            ['idVoucher' => 17, 'description' => "ABONO MENSUAL", 'amount' => 15412773.74, 'idVat' => 1, 'subtotalAmount' => 15412773.74],
        ];

        foreach ($voucherItems as $item) {
            VoucherItem::create($item);
        }
    }
}
