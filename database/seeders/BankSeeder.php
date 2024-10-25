<?php

namespace Database\Seeders;

use App\Models\Treasury\Bank\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $banks = [
            ['name' => 'BANCO GALICIA', 'street' => 'Teniente General Juan Domingo Perón', 'streetNumber' => 407, 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1038AAI', 'osm_id' => 186253156, 'latitude' => '-34.6052794', 'longitude' => '-58.372682770116164', 'phone' => '0800-888-4254', 'email' => 'goclientes@bancogalicia.com.ar', 'idUserCreated' => 1, 'updated_at' => null],            
            ['name' => 'BANCO NACION', 'street' => 'Bartolomé Mitre', 'streetNumber' => 326, 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => '1036', 'osm_id' => 3527701, 'latitude' => '-34.60715535', 'longitude' => '-58.371113160414986', 'phone' => '0810-666-4444', 'email' => 'bna@bna.com.ar', 'idUserCreated' => 1, 'updated_at' => null],
            ['name' => 'BANCO PROVINCIA DE BS AS', 'street' => 'San Martín', 'streetNumber' => 137, 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1004AAC', 'osm_id' => 476996831, 'latitude' => '-34.60657809999999', 'longitude' => '-58.373270491318536', 'phone' => '0810-222-2776', 'email' => 'serviciosalcliente@bpba.com.ar', 'idUserCreated' => 1, 'updated_at' => null],
            ['name' => 'BANCO BBVA', 'street' => 'Reconquista', 'streetNumber' => 2, 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1002AAB', 'osm_id' => 2175638871, 'latitude' => '-34.6077171', 'longitude' => '-58.372265', 'phone' => '0800-999-0303', 'email' => 'proteccionalusuario-arg@bbva.com', 'idUserCreated' => 1, 'updated_at' => null],
        ];

        foreach ($banks as $bank) {
            Bank::create($bank);
        }
    }
}
