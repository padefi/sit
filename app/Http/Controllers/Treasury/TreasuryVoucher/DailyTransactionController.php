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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Inertia\Response;
use Inertia\Inertia;
use Spatie\Browsershot\Browsershot;
use Spatie\Browsershot\Enums\Polling;

class DailyTransactionController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view daily transactions')->only(['index', 'show', 'generatePdf']);
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

    public function generatePdf($date) {
        $dailyTransactions = $this->show($date);
        $dailyTransactions = json_decode($dailyTransactions->getContent(), true);

        $previousInCash = $dailyTransactions['dailyTransactions'][1]['previousCash']  ?? 0;
        $previousOutCash = $dailyTransactions['dailyTransactions'][2]['previousCash'] ?? 0;
        $previousCash = $previousInCash - $previousOutCash;

        $dailyCashInTransactions = $dailyTransactions['dailyTransactions'][1]['cashTransactions'] ?? [];
        $dailyCashOutTransactions = $dailyTransactions['dailyTransactions'][2]['cashTransactions'] ?? [];

        $totalCashIn = collect($dailyTransactions['dailyTransactions'][1]['cashTransactions'])->sum('treasuryVoucher.totalAmount') ?? 0;
        $totalCashOut = collect($dailyTransactions['dailyTransactions'][2]['cashTransactions'])->sum('treasuryVoucher.totalAmount') ?? 0;
        $totalCash = $previousCash + $totalCashIn - $totalCashOut;

        $dailyBankInTransactions = $dailyTransactions['dailyTransactions'][1]['bankTransactions'] ?? [];
        $dailyBankOutTransactions = $dailyTransactions['dailyTransactions'][2]['bankTransactions'] ?? [];
        $totalBankIn = collect($dailyTransactions['dailyTransactions'][1]['bankTransactions'])->sum('treasuryVoucher.totalAmount') ?? 0;
        $totalBankOut = collect($dailyTransactions['dailyTransactions'][2]['bankTransactions'])->sum('treasuryVoucher.totalAmount') ?? 0;

        $dailyCheckInTransactions = $dailyTransactions['dailyTransactions'][1]['checkTransactions'] ?? [];
        $dailyCheckOutTransactions = $dailyTransactions['dailyTransactions'][2]['checkTransactions'] ?? [];
        $totalCheckIn = collect($dailyTransactions['dailyTransactions'][1]['checkTransactions'])->sum('treasuryVoucher.totalAmount') ?? 0;
        $totalCheckOut = collect($dailyTransactions['dailyTransactions'][2]['checkTransactions'])->sum('treasuryVoucher.totalAmount') ?? 0;

        $mpdf = new \Mpdf\Mpdf;
        $mpdf = new \Mpdf\Mpdf(['format' => 'A4', 'mode' => 'c', 'orientation' => 'L', 'margin_left' => 2, 'margin_right' => 2, 'margin_top' => 2, 'margin_header' => 5]);
        $mpdf->use_kwt = true;
        $mpdf->SetAutoPageBreak(true, 10);

        $mpdf->WriteHTML(View::make('dailyTransactionsPdf', [
            'date' => date('d/m/Y', strtotime($date)),

            'previousCash' => $previousCash,
            'totalCash' => $totalCash,

            'dailyCashInTransactions' => $dailyCashInTransactions,
            'dailyCashOutTransactions' => $dailyCashOutTransactions,
            'totalCashIn' => $totalCashIn,
            'totalCashOut' => $totalCashOut,

            'dailyBankInTransactions' => $dailyBankInTransactions,
            'dailyBankOutTransactions' => $dailyBankOutTransactions,
            'totalBankIn' => $totalBankIn,
            'totalBankOut' => $totalBankOut,

            'dailyCheckInTransactions' => $dailyCheckInTransactions,
            'dailyCheckOutTransactions' => $dailyCheckOutTransactions,
            'totalCheckIn' => $totalCheckIn,
            'totalCheckOut' => $totalCheckOut,
        ]));

        return $mpdf->Output();
    }
}
