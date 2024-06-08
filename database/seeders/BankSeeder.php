<?php

namespace Database\Seeders;

use App\Models\Treasury\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $banks = [
            ['name' => 'BANCO GALICIA', 'address' => 'PERON 407', 'phone' => '0800-888-4254', 'email' => 'goclientes@bancogalicia.com.ar', 'idUserCreated' => 1, 'updated_at' => null],
            ['name' => 'BANCO NACION', 'address' => 'MITRE 326', 'phone' => '0810-666-4444', 'email' => 'bna@bna.com.ar', 'idUserCreated' => 1, 'updated_at' => null],
            ['name' => 'BANCO PROVINCIA DE BS AS', 'address' => 'SAN MARTIN 137', 'phone' => '0810-222-2776', 'email' => 'serviciosalcliente@bpba.com.ar', 'idUserCreated' => 1, 'updated_at' => null],
            ['name' => 'BANCO BBVA', 'address' => 'RECONQUISTA 2', 'phone' => '0800-999-0303', 'email' => 'proteccionalusuario-arg@bbva.com', 'idUserCreated' => 1, 'updated_at' => null],
        ];

        foreach ($banks as $bank) {
            Bank::create($bank);
        }
    }
}
