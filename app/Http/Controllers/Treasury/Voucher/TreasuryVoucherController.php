<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Events\Treasury\Voucher\TreasuryVoucherEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Voucher\TreasuryVoucherRequest;
use App\Http\Resources\Treasury\Voucher\TreasuryVoucherResource;
use App\Http\Resources\Treasury\Voucher\TreasuryVoucherStatusResource;
use App\Models\Treasury\Voucher\TreasuryVoucher;
use App\Models\Treasury\Voucher\TreasuryVoucherStatus;
use App\Models\Treasury\Voucher\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Response;
use Inertia\Inertia;

class TreasuryVoucherController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view treasury vouchers')->only(['index', 'treasuryVouchers', 'treasuryVoucherStatus']);
        // $this->middleware('check.permission:view treasury vouchers')->only('treasuryVoucherStatus');
        $this->middleware('check.permission:edit treasury vouchers')->only('voidTreasuryVoucher');
        $this->middleware('check.permission:view users')->only('info');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response {
        return Inertia::render('Treasury/Voucher/TreasuryVouchers');
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
        $treasuryVouchers = TreasuryVoucher::orderBy('id', 'asc')->get();

        return response()->json([
            'treasuryVouchers' => TreasuryVoucherResource::collection($treasuryVouchers),
        ]);
    }

    public function treasuryVouchers(VoucherType $voucherType, string $status) {
        $treasuryVouchers = TreasuryVoucher::with(['userCreated', 'userUpdated'])
            ->where('idType', $voucherType->id)
            ->where('idVS', $status)
            ->orderBy('id', 'asc')
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

    public function voidTreasuryVoucher(TreasuryVoucher $treasuryVoucher) {
        $treasuryVoucherExist = TreasuryVoucher::where('id', $treasuryVoucher->id)->first();

        if (!$treasuryVoucherExist) {
            throw ValidationException::withMessages([
                'message' => trans('Comprobante no encontrado.')
            ]);
        }

        $treasuryVoucher = TreasuryVoucher::where('id', $treasuryVoucher->id)
            ->where('idVS', 1)
            ->first();

        if (!$treasuryVoucher) {
            throw ValidationException::withMessages([
                'message' => trans('El comprobante ha cambiado de estado.')
            ]);
        }

        $treasuryVoucher->update([
            'idVS' => 3,
        ]);

        $treasuryVoucher->load('userCreated', 'userUpdated');
        event(new TreasuryVoucherEvent($treasuryVoucher, $treasuryVoucher->id, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Comprobante aunlado exitosamente.',
                'voucher' => $treasuryVoucher,
            ],
            'success' => true,
        ]);
    }

    public function info(TreasuryVoucher $treasuryVoucher) {
        $treasuryVoucher = TreasuryVoucher::with(['userCreated', 'userUpdated'])->where('id', $treasuryVoucher->id)->first();

        if (!$treasuryVoucher) {
            throw ValidationException::withMessages([
                'message' => trans('Comprobante no encontrado.')
            ]);
        }

        return new TreasuryVoucherResource($treasuryVoucher);
    }
}
