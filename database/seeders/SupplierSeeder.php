<?php

namespace Database\Seeders;

use App\Models\Treasury\Supplier\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $suppliers = [
            ['name' => 'HOLDER SEGURIDAD S.A.', 'businessName' => 'HOLDER SEGURIDAD S.A.', 'cuit' => 30709641111, 'idVC' => 1, 'idCat' => 6, 'street' => 'Avenida Juan Bautista Justo', 'streetNumber' => 7910, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1407FBR', 'osm_id' => 266413439, 'latitude' => '-34.63210256530612', 'longitude' => '-58.49984647346939', 'phone' => '011-4635-0180', 'email' => 'info@holder-korpus.com', 'cbu' => '0070145320000005566633', 'incomeTaxWithholding' => 1, 'socialTax' => 0, 'vatTax' => 1, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'GRUB S.A.', 'businessName' => 'GRUB S.A.', 'cuit' => 30714647462, 'idVC' => 1, 'idCat' => 6, 'street' => 'Muñiz', 'streetNumber' => 1330, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => '1250', 'osm_id' => 422103697, 'latitude' => '-34.62970075', 'longitude' => '-58.42480595', 'phone' => '011-6749-7448', 'email' => 'info@grub.com.ar', 'cbu' => '0290052000000000251064', 'incomeTaxWithholding' => 1, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'ALFA MEDICA MEDICINA INTEGRAL', 'businessName' => 'ALFA MEDICA', 'cuit' => 30570999989, 'idVC' => 1, 'idCat' => 1, 'street' => 'Rodríguez Peña', 'streetNumber' => 237, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1033AAH', 'osm_id' => 360432605, 'latitude' => '-34.60628360204082', 'longitude' => '-58.390657985714284', 'phone' => '011-4382-033', 'email' => 'alfamedica@alfamedicasrl.com.ar', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'AGUA Y SANEAMIENTOS ARGENTINOS SOCIEDAD ANONIMA', 'businessName' => 'AySA', 'cuit' => 30709565075, 'idVC' => 1, 'idCat' => 1, 'street' => 'Valle', 'streetNumber' => 402, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => '1424', 'osm_id' => 2427523894, 'latitude' => '-34.6243973', 'longitude' => '-58.4332384', 'phone' => '0810-444-2972', 'email' => 'atencionalusuario@aysa.com.ar', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'NSS S.A.', 'businessName' => 'IPLAN', 'cuit' => 30702652975, 'idVC' => 1, 'idCat' => 6, 'street' => 'Reconquista', 'streetNumber' => 723, 'floor' => '2', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1054AAP', 'osm_id' => 207687554, 'latitude' => '-34.59773598333334', 'longitude' => '-58.37286312916667', 'phone' => '0800-345-0000', 'email' => 'iplaniv@iplan.com.ar', 'cbu' => '0720000720000002180922', 'incomeTaxWithholding' => 1, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'TELEFONICA ARGENTINA SOCIEDAD ANONIMA', 'businessName' => 'TELEFONICA S.A.', 'cuit' => 30711335532, 'idVC' => 1, 'idCat' => 1, 'street' => 'Avenida Ingeniero Huergo', 'streetNumber' => 1330, 'floor' => 'PB', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1042AAB', 'osm_id' => 404634808, 'latitude' => '-34.61602025306122', 'longitude' => '-58.36684735918367', 'phone' => '0800-321-0611', 'email' => 'contacto@telefonica.com.ar', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'TELEFONICA MOVILES ARGENTINA SOCIEDAD ANONIMA', 'businessName' => 'MOVISTAR', 'cuit' => 30678814357, 'idVC' => 1, 'idCat' => 1, 'street' => 'Avenida Corrientes', 'streetNumber' => 707, 'floor' => 'PB', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1043AAH', 'osm_id' => 212984804, 'latitude' => '-34.61602025306122', 'longitude' => '-58.37681983415559', 'phone' => '0800-321-0611', 'email' => 'atencionalcliente@movistar.com.ar', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'TELECOM ARGENTINA SOCIEDAD ANONIMA', 'businessName' => 'TELECOM S.A.', 'cuit' => 30639453738, 'idVC' => 1, 'idCat' => 1, 'street' => 'General Hornos', 'streetNumber' => 690, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1295ADM', 'osm_id' => 414538070, 'latitude' => '-34.60330556772585', 'longitude' => '-58.376896', 'phone' => '0800-888-0112', 'email' => 'clientes@telecom.com.ar', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'EMPRESA DISTRIBUIDORA DE ENERGIA SUR S.A.', 'businessName' => 'EDESUR S.A.', 'cuit' => 30655116512, 'idVC' => 1, 'idCat' => 1, 'street' => 'San José', 'streetNumber' => 140, 'floor' => '5', 'apartment' => '13', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1089AAB', 'osm_id' => 361987417, 'latitude' => '-34.61040826122449', 'longitude' => '-58.38614384693877', 'phone' => '0810-222-0200', 'email' => 'atencioncomercialt2@edesur.com.ar', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'COMPAÑIA DE SEGUROS LA MERCANTIL ANDINA S.A.', 'businessName' => 'Mercantil Andina Seguros S.A.', 'cuit' => 30500036911, 'idVC' => 1, 'idCat' => 6, 'street' => 'Avenida Belgrano', 'streetNumber' => 672, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => '1069', 'osm_id' => 208807479, 'latitude' => '-34.6128557', 'longitude' => '-58.375646585714286', 'phone' => '0810-888-6262', 'email' => 'walter.fortuna@lamercantil.com.ar', 'cbu' => '', 'incomeTaxWithholding' => 1, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'MUÑOZ MARCELO SEBASTIAN', 'businessName' => 'MUÑOZ MARCELO SEBASTIAN', 'cuit' => 20238528543, 'idVC' => 1, 'idCat' => 6, 'street' => 'Azcuénaga', 'streetNumber' => 461, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1046AAC', 'osm_id' => 359674472, 'latitude' => '-34.60393006122449', 'longitude' => '-58.400808120408165', 'phone' => '', 'email' => '', 'cbu' => '', 'incomeTaxWithholding' => 1, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'GOBIERNO DE LA CIUDAD DE BUENOS AIRES', 'businessName' => 'GOBIERNO DE LA CIUDAD DE BUENOS AIRES', 'cuit' => 34999032089, 'idVC' => 2, 'idCat' => 1, 'street' => 'Avenida Martín García', 'streetNumber' => 346, 'floor' => '1', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1143AAH', 'osm_id' => 405038451, 'latitude' => '-34.628946902040816', 'longitude' => '-58.369958985714284', 'phone' => '', 'email' => '', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'FERROTECNIA SRL', 'businessName' => 'FERROTECNIA SRL', 'cuit' => 30625882490, 'idVC' => 1, 'idCat' => 5, 'street' => 'Avenida Presidente Juan Domingo Perón', 'streetNumber' => 5750, 'floor' => '', 'apartment' => '', 'city' => 'Castelar', 'state' => 'Buenos Aires', 'country' => 'Argentina', 'postalCode' => '1688', 'osm_id' => 909050703, 'latitude' => '-34.63228846122449', 'longitude' => '-58.64276637755102', 'phone' => '', 'email' => '', 'cbu' => '', 'incomeTaxWithholding' => 1, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'BANCO DE GALICIA Y BUENOS AIRES S A U', 'businessName' => 'BANCO GALICIA', 'cuit' => 30500001735, 'idVC' => 1, 'idCat' => 1, 'street' => 'Teniente General Juan Domingo Perón', 'streetNumber' => 407, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1038AAI', 'osm_id' => 186253156, 'latitude' => '-34.6052794', 'longitude' => '-58.372682770116164', 'phone' => '0800-888-4254', 'email' => 'goclientes@bancogalicia.com.ar', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'BANCO DE LA NACION ARGENTINA', 'businessName' => 'BANCO NACION', 'cuit' => 30500010912, 'idVC' => 1, 'idCat' => 1, 'street' => 'Bartolomé Mitre', 'streetNumber' => 326, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => '1036', 'osm_id' => 3527701, 'latitude' => '-34.60715535', 'longitude' => '-58.371113160414986', 'phone' => '0810-666-4444', 'email' => 'bna@bna.com.ar', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'BANCO DE LA PROVINCIA DE BUENOS AIRES', 'businessName' => 'BANCO PROVINCIA DE BS AS', 'cuit' => 33999242109, 'idVC' => 1, 'idCat' => 1, 'street' => 'San Martín', 'streetNumber' => 137, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1004AAC', 'osm_id' => 476996831, 'latitude' => '-34.60657809999999', 'longitude' => '-58.373270491318536', 'phone' => '0810-222-2776', 'email' => 'serviciosalcliente@bpba.com.ar', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'BANCO BBVA ARGENTINA S.A.', 'businessName' => 'BANCO BBVA', 'cuit' => 30500003193, 'idVC' => 1, 'idCat' => 1, 'street' => 'Reconquista', 'streetNumber' => 2, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1038AAI', 'osm_id' => 2175638871, 'latitude' => '-34.6077171', 'longitude' => '-58.372265', 'phone' => '0800-999-0303', 'email' => 'proteccionalusuario-arg@bbva.com', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'ADMINISTRACION FEDERAL DE INGRESOS PUBLICOS', 'businessName' => 'A.F.I.P.', 'cuit' => 33693450239, 'idVC' => 2, 'idCat' => 1, 'street' => 'Avenida Hipólito Yrigoyen', 'streetNumber' => 360, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1086AAD', 'osm_id' => 208809079, 'latitude' => '-34.60898975714286', 'longitude' => '-58.37159734897959', 'phone' => '', 'email' => '', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'CORREO ARGENTINO S.A.', 'businessName' => 'CORREO ARGENTINO S.A.', 'cuit' => 30680792883, 'idVC' => 1, 'idCat' => 1, 'street' => 'Avenida San Juan', 'streetNumber' => 3001, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => '1231', 'osm_id' => 3660411172, 'latitude' => '-34.6242496', 'longitude' => '-58.4072626', 'phone' => '011-4957-1503', 'email' => '', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'CAMBEIRO MABEL BEATRIZ', 'businessName' => 'CAMBEIRO MABEL BEATRIZ', 'cuit' => 27101169877, 'idVC' => 1, 'idCat' => 8, 'street' => 'Avenida Córdoba', 'streetNumber' => 991, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1054AAI', 'osm_id' => 207686293, 'latitude' => '-34.59889563469388', 'longitude' => '-58.38100663877551', 'phone' => '', 'email' => '', 'cbu' => '', 'incomeTaxWithholding' => 1, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'METROGAS S A', 'businessName' => 'METROGAS S A', 'cuit' => 30657863676, 'idVC' => 1, 'idCat' => 1, 'street' => 'Bartolomé Mitre', 'streetNumber' => 737, 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => 'C1084ABB', 'osm_id' => 208803073, 'latitude' => '-34.60693015306122', 'longitude' => '-58.37700340204082', 'phone' => '0800-333-6427', 'email' => 'grandesclientesindustrias@metrogas.com.ar', 'cbu' => '', 'incomeTaxWithholding' => 0, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            ['name' => 'LODICO MATIAS ORLANDO', 'businessName' => 'DIAC SRL', 'cuit' => 20246727318, 'idVC' => 1, 'idCat' => 5, 'street' => 'Doctor Estanislao Severo Zeballos', 'streetNumber' => 6435, 'floor' => '', 'apartment' => '', 'city' => 'Wilde', 'state' => 'Buenos Aires', 'country' => 'Argentina', 'postalCode' => '1870', 'osm_id' => 699802932, 'latitude' => '-34.70430247346939', 'longitude' => '-58.31281818367347', 'phone' => '011-4217-2733', 'email' => 'ventas@diacsrl.com', 'cbu' => '0720053388000038020540', 'incomeTaxWithholding' => 1, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
            // ['name' => '', 'businessName' => '', 'cuit' => , 'idVC' => 1, 'idCat' => 1, 'street' => '', 'streetNumber' => , 'floor' => '', 'apartment' => '', 'city' => 'Ciudad de Buenos Aires', 'state' => 'Capital Federal', 'country' => 'Argentina', 'postalCode' => '1250', 'osm_id' => , 'latitude' => '', 'longitude' => '', 'phone' => '', 'email' => '', 'cbu' => '', 'incomeTaxWithholding' => 1, 'socialTax' => 0, 'vatTax' => 0, 'idUserCreated' => 1, 'created_at' => now(), 'updated_at' => null],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
