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
        $this->middleware('check.permission:view treasury vouchers, view daily transactions, view vouchers')->only(['index']);
    }

    public function index() {
        $dailyTransactionController = new DailyTransactionController();
        $dailyTransactions = $dailyTransactionController->show(Date::now()->format('Y-09-30'));

        $totalCashTransactions = 0;
        $totalBankTransactions = 0;
        $totalCheckTransactions = 0;

        foreach ($dailyTransactions->original['dailyTransactions'] as $voucherType) {
            $totalCashTransactions += count($voucherType['cashTransactions']);
            $totalBankTransactions += count($voucherType['bankTransactions']);
            $totalCheckTransactions += count($voucherType['checkTransactions']);
        }

        $totalTransactions = $totalCashTransactions + $totalBankTransactions + $totalCheckTransactions;

        $treasuryVoucherController = new TreasuryVoucherController();
        $incomeTreasuryVouchers = $treasuryVoucherController->treasuryVouchers(VoucherType::where('id', 1)->first(), 1);
        $expenseTreasuryVouchers = $treasuryVoucherController->treasuryVouchers(VoucherType::where('id', 2)->first(), 1);

        $totalIncomeTreasuryVouchers = count($incomeTreasuryVouchers->original['treasuryVouchers']);
        $totalExpenseTreasuryVouchers = count($expenseTreasuryVouchers->original['treasuryVouchers']);
        $totalTreasuryVouchers = $totalIncomeTreasuryVouchers + $totalExpenseTreasuryVouchers;

        $supplierController = new SupplierController();
        $suppliers = $supplierController->invoicePendingToPay();
        $totalInvoiceSuppliers = $suppliers->original['countInvoicePendingToPay'];

        return [
            'totalTransactions' => $totalTransactions,
            'totalTreasuryVouchers' => $totalTreasuryVouchers,
            'totalInvoiceSuppliers' => $totalInvoiceSuppliers,
        ];
    }
}
