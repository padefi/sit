<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(RolePermissionSeeder::class);
        $this->call(UserPermissionSeeder::class);
        $this->call(BankSeeder::class);
        $this->call(BankAccountTypeSeeder::class);
        $this->call(BankAccountSeeder::class);
        $this->call(VoucherTypeSeeder::class);
        $this->call(VoucherSubtypeSeeder::class);
        $this->call(VoucherExpenseSeeder::class);
        $this->call(VatConditionSeeder::class);
        $this->call(CategorySeeder::class);
    }
}
