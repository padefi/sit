<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Voucher\TreasuryVoucherRequest;
use App\Http\Resources\Treasury\Voucher\TreasuryVoucherResource;
use App\Models\Treasury\Voucher\TreasuryVoucher;
use Illuminate\Http\Request;

class TreasuryVoucherController extends Controller {
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
        $treasuryVouchers = TreasuryVoucher::with(['voucherType', 'voucherStatus', 'userCreated'])
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
}
