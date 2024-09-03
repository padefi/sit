<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Events\Treasury\Voucher\TreasuryVoucherEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Voucher\TreasuryVoucherRequest;
use App\Http\Resources\Treasury\Voucher\TreasuryVoucherResource;
use App\Http\Resources\Treasury\Voucher\TreasuryVoucherStatusResource;
use App\Models\Treasury\Supplier\Supplier;
use App\Models\Treasury\Taxes\IncomeTaxWithholding;
use App\Models\Treasury\Taxes\IncomeTaxWithholdingScale;
use App\Models\Treasury\Taxes\IncomeTaxWithholdingTable;
use App\Models\Treasury\Taxes\SocialSecurityTaxWithholding;
use App\Models\Treasury\Taxes\VatTaxWithholding;
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
        $this->middleware('check.permission:edit treasury vouchers')->only('calculateWithholdingTax');
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
        $treasuryVouchers = TreasuryVoucher::where('idSupplier', $id)->orderBy('id', 'asc')->get();

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

    public function calculateWithholdingTax(TreasuryVoucher $treasuryVoucher) {
        $tax = 21;
        $amountWithoutTax = round($treasuryVoucher->amount / (1 + ($tax / 100)), 2);
        $totalAmountCollected = $amountWithoutTax;
        $totalIncomeTaxAmountCollected = 0;
        $totalSocialTaxAmountCollected = 0;
        $totalVatTaxAmountCollected = 0;
        $incomeTaxWithholdingAmount = 0;
        $socialTaxAmount = 0;
        $vatTaxAmount = 0;
        $supplier = Supplier::where('id', $treasuryVoucher->idSupplier)->first();

        $pendingTreasuryVoucher = TreasuryVoucher::where('idSupplier', $supplier->id)
            ->where('idType', 2)
            ->where('idVS', 1)
            ->whereDate('created_at', '<=', date('Y-m-t'))
            ->whereNot('id', $treasuryVoucher->id)
            ->orderBy('id', 'asc')
            ->get();

        if ($pendingTreasuryVoucher->count() > 0) {
            $totalAmountCollected += round($pendingTreasuryVoucher->sum('amount') / (1 + ($tax / 100)), 2);
            $totalIncomeTaxAmountCollected += round($pendingTreasuryVoucher->sum('incomeTaxAmount'), 2);
            /* $totalSocialTaxAmountCollected += round($pendingTreasuryVoucher->sum('socialTaxAmount'), 2);
            $totalVatTaxAmountCollected += round($pendingTreasuryVoucher->sum('vatTaxAmount'), 2); */
        }

        $paidTreasuryVoucher = TreasuryVoucher::where('idSupplier', $supplier->id)
            ->where('idType', 2)
            ->where('idVS', 2)
            ->whereDate('confirmed_at', '<=', date('Y-m-t'))
            ->whereNot('id', $treasuryVoucher->id)
            ->orderBy('id', 'asc')
            ->get();

        if ($paidTreasuryVoucher->count() > 0) {
            $totalAmountCollected += round($paidTreasuryVoucher->sum('amount') / (1 + ($tax / 100)), 2);
            $totalIncomeTaxAmountCollected += round($paidTreasuryVoucher->sum('incomeTaxAmount'), 2);
            /* $totalSocialTaxAmountCollected += round($paidTreasuryVoucher->sum('socialTaxAmount'), 2);
            $totalVatTaxAmountCollected += round($paidTreasuryVoucher->sum('vatTaxAmount'), 2); */
        }

        if ($supplier->incomeTaxWithholding == 1) {
            $incomeTaxWithholdingTable = IncomeTaxWithholdingTable::where('idCat', $supplier->idCat)->first();

            $incomeTax = ($incomeTaxWithholdingTable->table === 'normal')
                ? IncomeTaxWithholding::where('idCat', $supplier->idCat)
                ->where('minAmount', '<=', $totalAmountCollected)
                ->where('startAt', '<=', date('Y-m-d'))
                ->where('endAt', '>=', date('Y-m-d'))
                ->first()
                : IncomeTaxWithholdingScale::where('idCat', $supplier->idCat)
                ->where('minAmount', '<=', $totalAmountCollected)
                ->where('maxAmount', '>=', $totalAmountCollected)
                ->where('startAt', '<=', date('Y-m-d'))
                ->where('endAt', '>=', date('Y-m-d'))
                ->first();

            $incomeTaxWithholdingAmount = $incomeTax
                ? round($incomeTax->fixedAmount + ($totalAmountCollected - $incomeTax->minAmount) * ($incomeTax->rate / 100) - $totalIncomeTaxAmountCollected, 2)
                : 0;
        }

        if ($supplier->socialTax == 1) {
            $socialTax = SocialSecurityTaxWithholding::where('idCat', $supplier->idCat)
                ->where('minAmount', '<=', $amountWithoutTax)
                ->where('startAt', '<=', date('Y-m-d'))
                ->where('endAt', '>=', date('Y-m-d'))
                ->first();

            $socialTaxAmount = $socialTax
                ? round($socialTax->fixedAmount + ($amountWithoutTax - $socialTax->minAmount) * ($socialTax->rate / 100) - $totalSocialTaxAmountCollected, 2)
                : 0;
        }

        if ($supplier->vatTax == 1) {
            $vatTax = VatTaxWithholding::where('idCat', $supplier->idCat)
                ->where('minAmount', '<=', $amountWithoutTax)
                ->where('startAt', '<=', date('Y-m-d'))
                ->where('endAt', '>=', date('Y-m-d'))
                ->first();

            $vatTaxAmount = $vatTax
                ? round($vatTax->fixedAmount + ($amountWithoutTax - $vatTax->minAmount) * ($vatTax->rate / 100) - $totalVatTaxAmountCollected, 2)
                : 0;
        }

        return response()->json([
            'incomeTaxWithholdingAmount' => $incomeTaxWithholdingAmount > 0 ? $incomeTaxWithholdingAmount : 0,
            'socialTaxAmount' => $socialTaxAmount > 0 ? $socialTaxAmount : 0,
            'vatTaxAmount' => $vatTaxAmount > 0 ? $vatTaxAmount : 0,
        ]);
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
