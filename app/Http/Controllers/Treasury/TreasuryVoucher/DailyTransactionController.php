<?php

namespace App\Http\Controllers\Treasury\TreasuryVoucher;

use App\Http\Controllers\Controller;
use App\Http\Resources\Treasury\TreasuryVoucher\TreasuryVoucherResource;
use App\Models\Treasury\TreasuryVoucher\TreasuryVoucher;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        return Inertia::render('Treasury/TreasuryVoucher/DailyTransactions');
    }

    public function show($date) {
        $treasuryVouchers = TreasuryVoucher::where('paymentDate', $date)
            ->where('idVS', 2)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'dailyTransactions' => TreasuryVoucherResource::collection($treasuryVouchers),
        ]);
    }
}
