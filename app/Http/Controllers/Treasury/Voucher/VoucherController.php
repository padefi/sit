<?php

namespace App\Http\Controllers;

use App\Models\Treasury\Voucher\Voucher;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Voucher\VoucherRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view vouchers')->only('index');
        $this->middleware('check.permission:create vouchers')->only('store');
        $this->middleware('check.permission:edit vouchers')->only('update');
        $this->middleware('check.permission:view users')->only('info');
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
    public function store(VoucherRequest $request) {
        $VoucherNumber = Voucher::where('idIt', $request->invoiceType, 'idITCode', $request->invoiceTypeCode, 'pointOfNumber', $request->pointOfNumber, 'invoiceNumber', $request->invoiceNumber)->first();

        if ($VoucherNumber) {
            throw ValidationException::withMessages([
                'message' => trans('El comprobante ya se encuentra ingresado.')
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Voucher $voucher) {
        //
    }
}
