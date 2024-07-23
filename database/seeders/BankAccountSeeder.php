<?php

namespace Database\Seeders;

use App\Models\Treasury\Bank\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $bankAccounts = [
            ['accountNumber' => '13422/4', 'cbu' => '0070002311100001342247', 'alias' => 'rojo.perro.puerta', 'idBank' => 1, 'idAT' => 1,  'idUserCreated' => 1, 'updated_at' => null, 'status' => true],
            ['accountNumber' => '9750122/6', 'cbu' => '0070002311100975012263', 'alias' => 'ballena.verde.jarron', 'idBank' => 1, 'idAT' => 2,  'idUserCreated' => 1, 'updated_at' => null, 'status' => true],
            ['accountNumber' => '16737/0', 'cbu' => '0110001311100001673701', 'alias' => 'silla.gato.amarillo', 'idBank' => 2, 'idAT' => 1,  'idUserCreated' => 1, 'updated_at' => null, 'status' => true],
            ['accountNumber' => '80082/02', 'cbu' => '0141000811100008008205', 'alias' => 'gris.mariposa.cajon', 'idBank' => 3, 'idAT' => 1,  'idUserCreated' => 1, 'updated_at' => null, 'status' => true],
        ];

        foreach ($bankAccounts as $bankAccount) {
            BankAccount::create($bankAccount);
        }
    }
}
