<?php

namespace Database\Seeders;

use App\Models\Treasury\Taxes\IncomeTaxWithholdingTable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IncomeTaxWithholdingTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $incomeTaxWithholdingTables = [
            ['idCat' => 2, 'table' => 'normal'],
            ['idCat' => 3, 'table' => 'normal'],
            ['idCat' => 4, 'table' => 'normal'],
            ['idCat' => 5, 'table' => 'normal'],
            ['idCat' => 6, 'table' => 'normal'],
            ['idCat' => 7, 'table' => 'scale'],
            ['idCat' => 8, 'table' => 'scale'],
            ['idCat' => 9, 'table' => 'normal'],
            ['idCat' => 10, 'table' => 'normal'],
        ];

        foreach ($incomeTaxWithholdingTables as $incomeTaxWithholdingTable) {
            IncomeTaxWithholdingTable::create($incomeTaxWithholdingTable);
        }
    }
}
