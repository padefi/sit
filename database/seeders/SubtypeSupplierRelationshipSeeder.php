<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\SubtypeSupplierRelationship;
use Illuminate\Database\Seeder;

class SubtypeSupplierRelationshipSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $subtypes = [
            // Proveedor AMUF
            ['idSubtype' => 1, 'idSupplier' => 1, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 5, 'idSupplier' => 1, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 9, 'idSupplier' => 1, 'idUserRelated' => 1, 'related_at' => now()],

            // Proveedor AFIP
            ['idSubtype' => 6, 'idSupplier' => 19, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 7, 'idSupplier' => 19, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 8, 'idSupplier' => 19, 'idUserRelated' => 1, 'related_at' => now()],

            // Anticipo a Proveedores
            ['idSubtype' => 2, 'idSupplier' => 2, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 2, 'idSupplier' => 3, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 2, 'idSupplier' => 4, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 2, 'idSupplier' => 12, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 2, 'idSupplier' => 14, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 2, 'idSupplier' => 20, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 2, 'idSupplier' => 21, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 2, 'idSupplier' => 23, 'idUserRelated' => 1, 'related_at' => now()],

            // Devolucion a Proveedores
            ['idSubtype' => 3, 'idSupplier' => 2, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 3, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 4, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 5, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 6, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 7, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 8, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 9, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 10, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 11, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 12, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 14, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 20, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 21, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 22, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 3, 'idSupplier' => 23, 'idUserRelated' => 1, 'related_at' => now()],

            // Pago a Proveedores
            ['idSubtype' => 4, 'idSupplier' => 2, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 3, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 4, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 5, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 6, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 7, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 8, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 9, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 10, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 11, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 12, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 13, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 14, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 20, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 21, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 22, 'idUserRelated' => 1, 'related_at' => now()],
            ['idSubtype' => 4, 'idSupplier' => 23, 'idUserRelated' => 1, 'related_at' => now()],
        ];

        foreach ($subtypes as $subtype) {
            SubtypeSupplierRelationship::create($subtype);
        }
    }
}
