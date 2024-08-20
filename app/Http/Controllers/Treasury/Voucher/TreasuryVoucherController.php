<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Voucher\TreasuryVoucherRequest;
use App\Http\Resources\Treasury\Voucher\TreasuryVoucherResource;
use App\Http\Resources\Treasury\Voucher\TreasuryVoucherStatusResource;
use App\Models\Treasury\Voucher\TreasuryVoucher;
use App\Models\Treasury\Voucher\TreasuryVoucherStatus;
use Illuminate\Http\Request;

class TreasuryVoucherController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view treasury vouchers')->only('show');
        $this->middleware('check.permission:view treasury vouchers')->only('treasuryVoucherStatus');
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TreasuryVoucherRequest $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $treasuryVouchers = TreasuryVoucher::with(['voucherType', 'voucherToTreasury.vouchers', 'voucherStatus', 'userCreated'])
            ->where('idSupplier', $id)
            ->get();

        return response()->json([
            'treasuryVouchers' => TreasuryVoucherResource::collection($treasuryVouchers),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
        //
    }

    public function treasuryVoucherStatus() {
        $treasuryVoucherStatus = TreasuryVoucherStatus::orderBy('id', 'asc')->get();

        return response()->json([
            'treasuryVoucherStatus' => TreasuryVoucherStatusResource::collection($treasuryVoucherStatus),
        ]);
    }
}
