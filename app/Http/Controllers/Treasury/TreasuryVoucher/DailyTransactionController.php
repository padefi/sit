<?php

namespace App\Http\Controllers\Treasury\TreasuryVoucher;

use App\Http\Controllers\Controller;
use App\Http\Resources\Treasury\TreasuryVoucher\BankTransactionResource;
use App\Http\Resources\Treasury\TreasuryVoucher\CashTransactionResource;
use App\Http\Resources\Treasury\TreasuryVoucher\CheckTransactionResource;
use App\Models\Treasury\TreasuryVoucher\BankTransaction;
use App\Models\Treasury\TreasuryVoucher\CashTransaction;
use App\Models\Treasury\TreasuryVoucher\CheckTransaction;
use App\Models\Treasury\Voucher\VoucherType;
use Inertia\Response;
use Inertia\Inertia;

class DailyTransactionController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view daily transactions')->only(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */

    public function index(): Response {
        return Inertia::render('Treasury/DailyTransactions/DailyTransactionsIndex');
    }

    public function show($date) {
        $voucherTypes = VoucherType::get();
        $dailyTransactions = [];

        foreach ($voucherTypes as $voucherType) {
            $previousCash = CashTransaction::with('treasuryVoucher')
                ->whereHas('treasuryVoucher', function ($query) use ($date, $voucherType) {
                    $query->where('paymentDate', '<', $date)
                        ->where('idVS', 2)
                        ->where('idType', $voucherType->id);
                })
                ->sum('amount');

            $cashTransactions = CashTransaction::with('treasuryVoucher')
                ->whereHas('treasuryVoucher', function ($query) use ($date, $voucherType) {
                    $query->where('paymentDate', $date)
                        ->where('idVS', 2)
                        ->where('idType', $voucherType->id);
                })
                ->orderBy('id', 'asc')
                ->get();

            $bankTransactions = BankTransaction::with('treasuryVoucher')
                ->whereHas('treasuryVoucher', function ($query) use ($date, $voucherType) {
                    $query->where('paymentDate', $date)
                        ->where('idVS', 2)
                        ->where('idType', $voucherType->id);
                })
                ->orderBy('id', 'asc')
                ->get();

            $checkTransactions = CheckTransaction::with('treasuryVoucher')
                ->whereHas('treasuryVoucher', function ($query) use ($date, $voucherType) {
                    $query->where('paymentDate', $date)
                        ->where('idVS', 2)
                        ->where('idType', $voucherType->id);
                })
                ->orderBy('id', 'asc')
                ->get();

            $dailyTransactions[$voucherType->id] = [
                'previousCash' => $previousCash,
                'cashTransactions' => CashTransactionResource::collection($cashTransactions),
                'bankTransactions' => BankTransactionResource::collection($bankTransactions),
                'checkTransactions' => CheckTransactionResource::collection($checkTransactions),
            ];
        }

        return response()->json([
            'dailyTransactions' => $dailyTransactions,
        ]);
    }
}
