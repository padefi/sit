<?php

namespace Database\Seeders;

use App\Models\Treasury\Taxes\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $category = [
            ['name' => 'NO APLICA'],
            ['name' => 'INTERESES'],
            ['name' => 'ALQUILERES'],
            ['name' => 'BIENES INMUEBLES URBANOS'],
            ['name' => 'ENAJENACIÓN DE BIENES MUEBLES'],
            ['name' => 'LOCACIÓN DE OBRAS Y SERV.'],
            ['name' => 'COMISIONES'],
            ['name' => 'PROFESIONES LIBERALES'],
            ['name' => 'OTRA CESIÓN O LOCACIÓN'],
            ['name' => 'TRANSFERENCIA DE DERECHOS, LLAVES, MARCAS Y PATENTES'],
        ];

        Category::withoutTimestamps(function () use ($category) {
            foreach ($category as $cate) {
                Category::create($cate);
            }
        });
    }
}
