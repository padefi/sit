<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\TypeSubtypeRelationship;
use App\Models\Treasury\Voucher\VoucherSubtype;
use Illuminate\Database\Seeder;

class TypeSubtypeRelationshipSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $subtypes = [
            // Ingresos
            ['idType' => 1, 'idSubtype' => 1, 'idUserRelated' => 1, 'related_at' => now()],
            ['idType' => 1, 'idSubtype' => 3, 'idUserRelated' => 1, 'related_at' => now()],
            ['idType' => 1, 'idSubtype' => 5, 'idUserRelated' => 1, 'related_at' => now()],
            ['idType' => 1, 'idSubtype' => 10, 'idUserRelated' => 1, 'related_at' => now()],

            // Egresos
            ['idType' => 2, 'idSubtype' => 1, 'idUserRelated' => 1, 'related_at' => now()],
            ['idType' => 2, 'idSubtype' => 2, 'idUserRelated' => 1, 'related_at' => now()],
            ['idType' => 2, 'idSubtype' => 4, 'idUserRelated' => 1, 'related_at' => now()],
            ['idType' => 2, 'idSubtype' => 6, 'idUserRelated' => 1, 'related_at' => now()],
            ['idType' => 2, 'idSubtype' => 7, 'idUserRelated' => 1, 'related_at' => now()],
            ['idType' => 2, 'idSubtype' => 8, 'idUserRelated' => 1, 'related_at' => now()],
            ['idType' => 2, 'idSubtype' => 9, 'idUserRelated' => 1, 'related_at' => now()],
        ];

        foreach ($subtypes as $subtype) {
            TypeSubtypeRelationship::create($subtype);
        }
    }
}
