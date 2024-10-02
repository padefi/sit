<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Treasury\Supplier\SupplierController;
use App\Http\Controllers\Treasury\TreasuryVoucher\DailyTransactionController;
use App\Http\Controllers\Treasury\TreasuryVoucher\TreasuryVoucherController;
use App\Models\Treasury\Voucher\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class DashboardController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view treasury vouchers,view daily transactions,view vouchers')->only(['index']);
    }

    public function index() {
        /* Treasury Vouchers */
        $treasuryVoucherController = new TreasuryVoucherController();
        $incomeTreasuryVouchers = $treasuryVoucherController->treasuryVouchers(VoucherType::where('id', 1)->first(), 1);
        $expenseTreasuryVouchers = $treasuryVoucherController->treasuryVouchers(VoucherType::where('id', 2)->first(), 1);

        $totalIncomeTreasuryVouchers = count($incomeTreasuryVouchers->original['treasuryVouchers']);
        $totalExpenseTreasuryVouchers = count($expenseTreasuryVouchers->original['treasuryVouchers']);
        $totalTreasuryVouchers = $totalIncomeTreasuryVouchers + $totalExpenseTreasuryVouchers;
        /* Treasury Vouchers */

        /* Daily Transactions */
        $dailyTransactionController = new DailyTransactionController();
        $dailyTransactions = $dailyTransactionController->show(Date::now()->format('Y-m-d'));

        $totalCashTransactions = 0;
        $totalBankTransactions = 0;
        $totalCheckTransactions = 0;

        foreach ($dailyTransactions->original['dailyTransactions'] as $voucherType) {
            $totalCashTransactions += count($voucherType['cashTransactions']);
            $totalBankTransactions += count($voucherType['bankTransactions']);
            $totalCheckTransactions += count($voucherType['checkTransactions']);
        }

        $totalTransactions = $totalCashTransactions + $totalBankTransactions + $totalCheckTransactions;
        /* Daily Transactions */

        /* Invoice Suppliers pending to pay */
        $supplierController = new SupplierController();
        $suppliers = $supplierController->invoicePendingToPay();
        $totalInvoiceSuppliers = $suppliers->original['countInvoicePendingToPay'];
        /* Invoice Suppliers pending to pay */

        return [
            'totalTreasuryVouchers' => $totalTreasuryVouchers,
            'totalTransactions' => $totalTransactions,
            'totalInvoiceSuppliers' => $totalInvoiceSuppliers,
        ];
    }
}
