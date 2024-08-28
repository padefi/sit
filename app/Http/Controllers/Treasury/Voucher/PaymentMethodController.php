<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Http\Controllers\Controller;
use App\Http\Resources\Treasury\Voucher\PaymentMethodResource;
use App\Models\Treasury\Voucher\PaymentMethod;

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
