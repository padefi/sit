<?php

namespace App\Http\Controllers\Treasury\TreasuryVoucher;

use App\Http\Controllers\Controller;
use App\Http\Resources\Treasury\TreasuryVoucher\PaymentMethodResource;
use App\Models\Treasury\TreasuryVoucher\PaymentMethod;

class PaymentMethodController extends Controller {
    public function index() {
        $paymentMethods = PaymentMethod::orderBy('name', 'asc')->get();

        return response()->json([
            'paymentMethods' => PaymentMethodResource::collection($paymentMethods),
        ]);
    }

    public function show(string $id) {
        $voucherType = $id === 1 ? 1 : 3; // 1 = Transferencia, 3 = Depósito
        $paymentMethods = PaymentMethod::where('id', '!=', $voucherType)->orderBy('name', 'asc')->get();

        return response()->json([
            'paymentMethods' => PaymentMethodResource::collection($paymentMethods),
        ]);
    }
}
