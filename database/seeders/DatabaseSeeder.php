<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    /**
     * Seed the application's database.
     */
    public function run(): void {
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
        $this->call(TypeSubtypeRelationshipSeeder::class);
        $this->call(SubtypeExpenseRelationshipSeeder::class);
        $this->call(VatConditionSeeder::class);
        $this->call(VatRateSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(TaxTypeSeeder::class);
        $this->call(IncomeTaxWithholdingSeeder::class);
        $this->call(IncomeTaxWithholdingScaleSeeder::class);
        $this->call(IncomeTaxWithholdingTableSeeder::class);
        $this->call(SocialSecurityTaxWithholdingSeeder::class);
        $this->call(VatTaxWithholdingSeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(SubtypeSupplierRelationshipSeeder::class);
        $this->call(PayConditionSeeder::class);
        $this->call(InvoiceTypeSeeder::class);
        $this->call(InvoiceTypeCodeSeeder::class);
        $this->call(VoucherTypeInvoiceTypeRelationshipSeeder::class);
        $this->call(InvoiceTypeInvoiceTypeCodeRelationshipSeeder::class);
        $this->call(VoucherSeeder::class);
        $this->call(VoucherItemSeeder::class);
        $this->call(PaymentMethodSeeder::class);
        $this->call(TreasuryVoucherStatusSeeder::class);
        $this->call(TreasuryVoucherSeeder::class);        
        $this->call(VoucherToTreasurySeeder::class);
        $this->call(TreasuryCustomVoucherSeeder::class);
        $this->call(CashTransactionSeeder::class);
    }
}
