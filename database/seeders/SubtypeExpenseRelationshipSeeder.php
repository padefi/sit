<?php

namespace Database\Seeders;

use App\Models\Treasury\Voucher\SubtypeExpenseRelationship;
use App\Models\Treasury\Voucher\VoucherExpense;
use Illuminate\Database\Seeder;

class SubtypeExpenseRelationshipSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $allVoucherExpenses = VoucherExpense::all();

        foreach ($allVoucherExpenses as $voucherExpense) {
            $expenses = [
                ['idSubtype' => 2, 'idExpense' => $voucherExpense->id, 'idUserRelated' => 1, 'related_at' => now()],
                ['idSubtype' => 3, 'idExpense' => $voucherExpense->id, 'idUserRelated' => 1, 'related_at' => now()],
                ['idSubtype' => 4, 'idExpense' => $voucherExpense->id, 'idUserRelated' => 1, 'related_at' => now()],
            ];

            foreach ($expenses as $expense) {
                SubtypeExpenseRelationship::create($expense);
            }
        }
    }
}
