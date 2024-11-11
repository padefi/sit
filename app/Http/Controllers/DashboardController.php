<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Treasury\Supplier\SupplierController;
use App\Http\Controllers\Treasury\TreasuryVoucher\DailyTransactionController;
use App\Http\Controllers\Treasury\TreasuryVoucher\TreasuryVoucherController;
use App\Models\Treasury\Voucher\VoucherType;
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
        $amountIncomeTreasuryVouchers = array_sum($incomeTreasuryVouchers->original['treasuryVouchers']->pluck('totalAmount')->toArray());

        $totalExpenseTreasuryVouchers = count($expenseTreasuryVouchers->original['treasuryVouchers']);
        $amountExpenseTreasuryVouchers = array_sum($expenseTreasuryVouchers->original['treasuryVouchers']->pluck('totalAmount')->toArray());

        $totalTreasuryVouchers = $totalIncomeTreasuryVouchers + $totalExpenseTreasuryVouchers;
        /* Treasury Vouchers */

        /* Daily Transactions */
        $dailyTransactionController = new DailyTransactionController();
        $dailyTransactions = $dailyTransactionController->show(Date::now()->format('Y-m-d'));

        $totalIncomeCashTransactions = 0;
        $amountIncomeCashTransactions = 0;
        $totalExpenseCashTransactions = 0;
        $amountExpenseCashTransactions = 0;

        $totalIncomeBankTransactions = 0;
        $amountIncomeBankTransactions = 0;
        $totalExpenseBankTransactions = 0;
        $amountExpenseBankTransactions = 0;

        $totalIncomeCheckTransactions = 0;
        $amountIncomeCheckTransactions = 0;
        $totalExpenseCheckTransactions = 0;
        $amountExpenseCheckTransactions = 0;

        foreach ($dailyTransactions->original['dailyTransactions'] as $voucherType) {
            $totalIncomeCashTransactions += count($voucherType['cashTransactions']->filter(function ($transaction) {
                return $transaction->TreasuryVoucher->idType === 1;
            }));
            $amountIncomeCashTransactions += $voucherType['cashTransactions']->filter(function ($transaction) {
                return $transaction->TreasuryVoucher->idType === 1;
            })->pluck('amount')->sum();

            $totalExpenseCashTransactions += count($voucherType['cashTransactions']->filter(function ($transaction) {
                return $transaction->TreasuryVoucher->idType === 2;
            }));
            $amountExpenseCashTransactions += $voucherType['cashTransactions']->filter(function ($transaction) {
                return $transaction->TreasuryVoucher->idType === 2;
            })->pluck('amount')->sum();

            $totalIncomeBankTransactions += count($voucherType['bankTransactions']->filter(function ($transaction) {
                return $transaction->TreasuryVoucher->idType === 1;
            }));
            $amountIncomeBankTransactions += $voucherType['bankTransactions']->filter(function ($transaction) {
                return $transaction->TreasuryVoucher->idType === 1;
            })->pluck('amount')->sum();

            $totalExpenseBankTransactions += count($voucherType['bankTransactions']->filter(function ($transaction) {
                return $transaction->TreasuryVoucher->idType === 2;
            }));
            $amountExpenseBankTransactions += $voucherType['bankTransactions']->filter(function ($transaction) {
                return $transaction->TreasuryVoucher->idType === 2;
            })->pluck('amount')->sum();

            $totalIncomeCheckTransactions += count($voucherType['checkTransactions']->filter(function ($transaction) {
                return $transaction->TreasuryVoucher->idType === 1;
            }));
            $amountIncomeCheckTransactions += $voucherType['checkTransactions']->filter(function ($transaction) {
                return $transaction->TreasuryVoucher->idType === 1;
            })->pluck('amount')->sum();

            $totalExpenseCheckTransactions += count($voucherType['checkTransactions']->filter(function ($transaction) {
                return $transaction->TreasuryVoucher->idType === 2;
            }));
            $amountExpenseCheckTransactions += $voucherType['checkTransactions']->filter(function ($transaction) {
                return $transaction->TreasuryVoucher->idType === 2;
            })->pluck('amount')->sum();
        }

        $totalIncomeTransactions = $totalIncomeCashTransactions + $totalIncomeBankTransactions + $totalIncomeCheckTransactions;
        $amountIncomeTransactions = $amountIncomeCashTransactions + $amountIncomeBankTransactions + $amountIncomeCheckTransactions;

        $totalExpenseTransactions = $totalExpenseCashTransactions + $totalExpenseBankTransactions + $totalExpenseCheckTransactions;
        $amountExpenseTransactions = $amountExpenseCashTransactions + $amountExpenseBankTransactions + $amountExpenseCheckTransactions;

        $totalTransactions = $totalIncomeTransactions + $totalExpenseTransactions;
        /* Daily Transactions */

        /* Invoice Suppliers pending to pay */
        $supplierController = new SupplierController();
        $suppliers = $supplierController->invoicePendingToPay();
        $totalInvoiceSuppliers = $suppliers->original['countInvoicePendingToPay'];
        $amountIncomeInvoiceSuppliers = $suppliers->original['incomePendingToPay'];
        $amountExpenseInvoiceSuppliers = $suppliers->original['expensePendingToPay'];
        /* Invoice Suppliers pending to pay */

        return [
            'treasuryVouchers' => [
                'totalTreasuryVouchers' => $totalTreasuryVouchers,
                // 'totalIncomeTreasuryVouchers' => $totalIncomeTreasuryVouchers,
                'amountIncomeTreasuryVouchers' => $amountIncomeTreasuryVouchers,
                // 'totalExpenseTreasuryVouchers' => $totalExpenseTreasuryVouchers,
                'amountExpenseTreasuryVouchers' => $amountExpenseTreasuryVouchers,
            ],
            'totalTransactions' => [
                'totalTransactions' => $totalTransactions,
                // 'amountIncomeTransactions' => $amountIncomeTransactions,
                'amountIncomeTransactions' => $amountIncomeTransactions,
                // 'totalExpenseTransactions' => $totalExpenseTransactions,
                'amountExpenseTransactions' => $amountExpenseTransactions,
            ],
            'totalInvoiceSuppliers' => [
                'totalInvoiceSuppliers' => $totalInvoiceSuppliers,
                'amountIncomeInvoiceSuppliers' => $amountIncomeInvoiceSuppliers,
                'amountExpenseInvoiceSuppliers' => $amountExpenseInvoiceSuppliers,
            ],
        ];
    }
}
