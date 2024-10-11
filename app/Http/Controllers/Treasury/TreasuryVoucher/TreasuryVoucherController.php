<?php

namespace App\Http\Controllers\Treasury\TreasuryVoucher;

use App\Events\Treasury\TreasuryVoucher\TreasuryVoucherEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\TreasuryVoucher\TreasuryCustomVoucherRequest;
use App\Http\Resources\Treasury\TreasuryVoucher\TreasuryCustomVoucherResource;
use App\Http\Resources\Treasury\TreasuryVoucher\TreasuryVoucherResource;
use App\Http\Resources\Treasury\TreasuryVoucher\TreasuryVoucherStatusResource;
use App\Models\Treasury\Supplier\Supplier;
use App\Models\Treasury\Taxes\IncomeTaxWithholding;
use App\Models\Treasury\Taxes\IncomeTaxWithholdingScale;
use App\Models\Treasury\Taxes\IncomeTaxWithholdingTable;
use App\Models\Treasury\Taxes\SocialSecurityTaxWithholding;
use App\Models\Treasury\Taxes\VatTaxWithholding;
use App\Models\Treasury\TreasuryVoucher\BankTransaction;
use App\Models\Treasury\TreasuryVoucher\CashTransaction;
use App\Models\Treasury\TreasuryVoucher\CheckTransaction;
use App\Models\Treasury\TreasuryVoucher\TreasuryCustomVoucher;
use App\Models\Treasury\TreasuryVoucher\TreasuryVoucher;
use App\Models\Treasury\TreasuryVoucher\TreasuryVoucherStatus;
use App\Models\Treasury\TreasuryVoucher\TreasuryVoucherTaxWithholding;
use App\Models\Treasury\Voucher\Voucher;
use App\Models\Treasury\Voucher\VoucherToTreasury;
use App\Models\Treasury\Voucher\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Response;
use Inertia\Inertia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TreasuryVoucherController extends Controller {
    protected $vouchersIds = []; //This value store the ids of the vouchers for been stored in calculateTotalAmountCollected function to not repeat them.

    public function __construct() {
        $this->middleware('check.permission:view treasury vouchers')->only(['index', 'exportTreasuryVouchers', 'treasuryCustomVouchers', 'treasuryVouchers', 'countTreasuryVouchers', 'treasuryVoucherStatus', 'validateTransactionNumber']);
        $this->middleware('check.permission:edit treasury vouchers')->only(['update', 'voidTreasuryVoucher', 'calculateWithholdingTax', 'confirmTreasuryVoucher']);
        $this->middleware('check.permission:view users')->only('info');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response {
        return Inertia::render('Treasury/TreasuryVoucher/TreasuryVouchers');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TreasuryCustomVoucherRequest $request) {
        $treasuryVoucher = TreasuryVoucher::create([
            'idType' => $request->voucherType,
            'idSupplier' => $request->supplier,
            'idVS' => 1,
            'amount' => $request->amount,
            'totalAmount' => $request->amount,
            'idUserCreated' => Auth::id(),
            'created_at' => now(),
            'updated_at' => null,
        ]);

        TreasuryCustomVoucher::create([
            'idTV' => $treasuryVoucher->id,
            'idSupplier' => $request->supplier,
            'idType' => $request->voucherType,
            'idSubtype' => $request->voucherSubtype,
            'idExpense' => $request->voucherExpense > 0 ? $request->voucherExpense : null,
            'amount' => $request->amount,
            'notes' => strtoupper($request->notes),
            'voucherDate' => date('Y-m-d', strtotime($request->voucherDate)),
            'idUserCreated' => Auth::id(),
            'created_at' => now(),
            'updated_at' => null,
        ]);

        $treasuryVoucher->load('userCreated', 'userUpdated');
        event(new TreasuryVoucherEvent($treasuryVoucher, $treasuryVoucher->id, 'create'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Comprobante cargado exitosamente.',
                'treasuryVoucher' => $treasuryVoucher,
            ],
            'success' => true,
        ]);
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
    public function update(TreasuryCustomVoucherRequest $request, TreasuryCustomVoucher $treasuryCustomVoucher) {
        $treasuryVoucher = TreasuryVoucher::where('id', $treasuryCustomVoucher->idTV)->first();

        if (!$treasuryVoucher) {
            throw ValidationException::withMessages([
                'message' => trans('Comprobante no encontrado.')
            ]);
        }

        if ($treasuryVoucher->idVS != 1) {
            throw ValidationException::withMessages([
                'message' => trans('El comprobante ha cambiado de estado.')
            ]);
        }

        $treasuryVoucher->update([
            'idType' => $request->voucherType,
            'idSupplier' => $request->supplier,
            'amount' => $request->amount,
            'totalAmount' => $request->amount,
            'idUserUpdated' => Auth::id(),
            'updated_at' => now(),
        ]);

        $treasuryCustomVoucher->update([
            'idSupplier' => $request->supplier,
            'idType' => $request->voucherType,
            'idSubtype' => $request->voucherSubtype,
            'idExpense' => $request->voucherExpense > 0 ? $request->voucherExpense : null,
            'amount' => $request->amount,
            'notes' => strtoupper($request->notes),
            'voucherDate' => date('Y-m-d', strtotime($request->voucherDate)),
            'idUserUpdated' => Auth::id(),
            'updated_at' => now(),
        ]);

        $treasuryVoucher->load('userCreated', 'userUpdated');
        event(new TreasuryVoucherEvent($treasuryVoucher, $treasuryVoucher->id, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Comprobante actualizado exitosamente.',
            ],
            'success' => true,
        ]);
    }

    public function treasuryCustomVouchers(TreasuryCustomVoucher $treasuryCustomVoucher) {
        $treasuryCustomVouchers = TreasuryCustomVoucher::where('id', $treasuryCustomVoucher->id)->get();

        return response()->json([
            'treasuryCustomVouchers' => TreasuryCustomVoucherResource::collection($treasuryCustomVouchers),
        ]);
    }

    public function treasuryVouchers(VoucherType $voucherType, string $status) {
        $treasuryVouchers = TreasuryVoucher::with(['userCreated', 'userUpdated', 'bankTransaction', 'cashTransaction', 'checkTransaction'])
            ->where('idType', $voucherType->id)
            ->where('idVS', $status)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'treasuryVouchers' => TreasuryVoucherResource::collection($treasuryVouchers),
        ]);
    }

    public function countTreasuryVouchers() {
        foreach (VoucherType::all() as $voucherType) {
            $treasuryVouchers = TreasuryVoucher::where('idType', $voucherType->id)
                ->where('idVS', 1)
                ->count();

            $countTreasuryVouchers[] = [
                'voucherType' => $voucherType->id,
                'count' => $treasuryVouchers,
            ];
        }

        return response()->json([
            'countTreasuryVouchers' => $countTreasuryVouchers,
        ]);
    }

    public function calculateWithholdingTax(Request $request, TreasuryVoucher $treasuryVoucher) {
        $voucherIds = $request->input('voucherIds', []);

        if (!is_array($voucherIds) || empty($voucherIds)) {
            throw ValidationException::withMessages([
                'message' => trans('No se proporcionaron comprobantes válidos.')
            ]);
        }

        if (!$treasuryVoucher) {
            throw ValidationException::withMessages([
                'message' => trans('Comprobante no encontrado.')
            ]);
        }

        $tax = 21;
        // $amountWithoutTax = $this->calculateTotalAmountCollected($treasuryVoucher, $tax) - $this->withheldTaxVoucher($treasuryVoucher, 1)['amountWithoutTax'];
        $amountWithoutTax = $this->calculateTotalAmountCollected($treasuryVoucher, $tax);
        $totalAmountCollected = $amountWithoutTax;
        $totalIncomeTaxAmountCollected = 0;
        $totalSocialTaxAmountCollected = 0;
        $totalVatTaxAmountCollected = 0;
        /* $totalIncomeTaxAmountCollected = $this->withheldTaxVoucher($treasuryVoucher, 1)['amountWithheldTax'];
        $totalSocialTaxAmountCollected = $this->withheldTaxVoucher($treasuryVoucher, 2)['amountWithheldTax'];
        $totalVatTaxAmountCollected = $this->withheldTaxVoucher($treasuryVoucher, 3)['amountWithheldTax']; */
        $incomeTaxWithholdingAmount = 0;
        $socialTaxAmount = 0;
        $vatTaxAmount = 0;
        $supplier = Supplier::where('id', $treasuryVoucher->idSupplier)->first();

        $pendingTreasuryVoucher = TreasuryVoucher::where('idSupplier', $supplier->id)
            ->where('idType', 2)
            ->where('idVS', 1)
            ->whereDate('created_at', '<=', date('Y-m-t'))
            ->whereIn('id', $voucherIds)
            ->whereNot('id', $treasuryVoucher->id)
            ->orderBy('id', 'asc')
            ->get();

        if ($pendingTreasuryVoucher->count() > 0) {
            foreach ($pendingTreasuryVoucher as $voucher) {
                // $totalAmountCollected += $this->calculateTotalAmountCollected($voucher, $tax);
                $totalAmountCollected += $this->calculateTotalAmountCollected($voucher, $tax) - $this->withheldTaxVoucher($voucher, 1)['amountWithoutTax'];
            }

            $totalIncomeTaxAmountCollected += round($pendingTreasuryVoucher->sum('incomeTaxAmount'), 2);
            // $totalIncomeTaxAmountCollected = $this->withheldTaxVoucher($treasuryVoucher, 1)['amountWithheldTax'] + round($pendingTreasuryVoucher->sum('incomeTaxAmount'), 2);
        }

        $paidTreasuryVoucher = TreasuryVoucher::where('idSupplier', $supplier->id)
            ->where('idType', 2)
            ->where('idVS', 2)
            ->whereDate('paymentDate', '>=', date('Y-m-1'))
            ->whereDate('paymentDate', '<=', date('Y-m-t'))
            ->whereNot('id', $treasuryVoucher->id)
            ->orderBy('id', 'asc')
            ->get();

        if ($paidTreasuryVoucher->count() > 0) {
            foreach ($paidTreasuryVoucher as $voucher) {
                $totalAmountCollected += $this->calculateTotalAmountCollected($voucher, $tax);
                // $totalAmountCollected += $this->calculateTotalAmountCollected($voucher, $tax) - $this->withheldTaxVoucher($voucher, 1)['amountWithoutTax'];
            }

            $totalIncomeTaxAmountCollected += round($paidTreasuryVoucher->sum('incomeTaxAmount'), 2);
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

    public function validateTransactionNumber(Request $request) {
        $bankAccountId = $request->input('bankAccountId');
        $paymentMethod = $request->input('paymentMethod');
        $transactionNumber = $request->input('transactionNumber');
        $transaction = null;

        if ($paymentMethod == 1 || $paymentMethod == 3) {
            $transaction = BankTransaction::where('number', $transactionNumber)
                ->where('idBA', $bankAccountId)
                ->where('status', 1)
                ->first();
        }

        if ($paymentMethod == 2) {
            $transaction = CheckTransaction::where('idBA', $bankAccountId)
                ->where('number', $transactionNumber)
                ->where('status', 1)
                ->first();
        }

        return response()->json([
            'transaction' => $transaction ? true : false,
        ]);
    }

    public function confirmTreasuryVoucher(Request $request) {
        foreach ($request->input('vouchers', []) as $item) {
            $treasuryVoucherExist = TreasuryVoucher::with(['voucherToTreasury'])->where('id', $item['id'])
                ->where('idSupplier', $item['supplierId'])
                ->first();

            if (!$treasuryVoucherExist) {
                throw ValidationException::withMessages([
                    'message' => trans('Comprobante no encontrado.')
                ]);
            }

            if ($treasuryVoucherExist->idVS === 2) {
                throw ValidationException::withMessages([
                    'message' => trans('El comprobante ya ha sido confirmado.')
                ]);
            }

            if ($treasuryVoucherExist->idVS === 3) {
                throw ValidationException::withMessages([
                    'message' => trans('El comprobante ya ha sido anulado.')
                ]);
            }

            if ($item['paymentMethod'] == 2) {
                $transaction = CheckTransaction::where('idBA', $item['bankAccountId'])
                    ->where('number', $item['transactionNumber'])
                    ->where('status', 1)
                    ->first();

                if ($transaction) {
                    throw ValidationException::withMessages([
                        'message' => trans('El número de operación ya ha sido utilizado.')
                    ]);
                }
            }
        }

        foreach ($request->input('vouchers', []) as $item) {
            $treasuryVoucher = TreasuryVoucher::where('id', $item['id'])
                ->where('idSupplier', $item['supplierId'])
                ->first();

            $treasuryVoucher->update([
                'idVS' => 2,
                'idPM' => $item['paymentMethod'],
                'idBA' => $item['bankAccountId'] > 0 ? $item['bankAccountId'] : null,
                'number' => $item['transactionNumberStatus'] === 1 ? strtoupper($item['transactionNumber']) : null,
                'incomeTaxAmount' => $item["withholdings"]['incomeTax'],
                'socialTaxAmount' => $item["withholdings"]['socialTax'],
                'vatTaxAmount' => $item["withholdings"]['vatTax'],
                'totalAmount' => $item['totalAmount'],
                'paymentDate' => date('Y-m-d', strtotime($item['paymentDate'])),
                'idUserConfirmed' => Auth::id(),
                'confirmed_at' => now(),
            ]);

            if ($item["withholdings"]['incomeTax'] > 0) {
                $incomeTaxTreasuryVoucher = TreasuryVoucher::create([
                    'idType' => 2,
                    'idSupplier' => 19, // A.F.I.P.
                    'idVS' => 1,
                    'amount' => $item["withholdings"]['incomeTax'],
                    'totalAmount' => $item["withholdings"]['incomeTax'],
                    'idUserCreated' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => null,
                ]);

                TreasuryVoucherTaxWithholding::create([
                    'idOTV' => $treasuryVoucher->id,
                    'idNTV' => $incomeTaxTreasuryVoucher->id,
                    'idTT' => 1,
                    'amount' => $item["withholdings"]['incomeTax'],
                    'idUserCreated' => Auth::id(),
                    'created_at' => now(),
                ]);

                event(new TreasuryVoucherEvent($incomeTaxTreasuryVoucher, $incomeTaxTreasuryVoucher->id, 'create'));
            }

            if ($item["withholdings"]['socialTax'] > 0) {
                $socialTaxTreasuryVoucher = TreasuryVoucher::create([
                    'idType' => 2,
                    'idSupplier' => 19, // A.F.I.P.
                    'idVS' => 1,
                    'amount' => $item["withholdings"]['socialTax'],
                    'totalAmount' => $item["withholdings"]['socialTax'],
                    'idUserCreated' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => null,
                ]);

                TreasuryVoucherTaxWithholding::create([
                    'idOTV' => $treasuryVoucher->id,
                    'idNTV' => $socialTaxTreasuryVoucher->id,
                    'idTT' => 2,
                    'amount' => $item["withholdings"]['socialTax'],
                    'idUserCreated' => Auth::id(),
                    'created_at' => now(),
                ]);

                event(new TreasuryVoucherEvent($socialTaxTreasuryVoucher, $socialTaxTreasuryVoucher->id, 'create'));
            }

            if ($item["withholdings"]['vatTax'] > 0) {
                $vatTaxTreasuryVoucher = TreasuryVoucher::create([
                    'idType' => 2,
                    'idSupplier' => 19, // A.F.I.P.
                    'idVS' => 1,
                    'amount' => $item["withholdings"]['vatTax'],
                    'totalAmount' => $item["withholdings"]['vatTax'],
                    'idUserCreated' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => null,
                ]);

                TreasuryVoucherTaxWithholding::create([
                    'idOTV' => $treasuryVoucher->id,
                    'idNTV' => $vatTaxTreasuryVoucher->id,
                    'idTT' => 3,
                    'amount' => $item["withholdings"]['vatTax'],
                    'idUserCreated' => Auth::id(),
                    'created_at' => now(),
                ]);

                event(new TreasuryVoucherEvent($vatTaxTreasuryVoucher, $vatTaxTreasuryVoucher->id, 'create'));
            }

            switch ($item['paymentMethod']) {
                case 1:
                case 3:
                    BankTransaction::create([
                        'idBA' => $item['bankAccountId'],
                        'idTV' => $item['id'],
                        'number' => strtoupper($item['transactionNumber']),
                        'amount' => $item['totalAmount'],
                        'idUserConfirmed' => Auth::id(),
                        'confirmed_at' => now(),
                        'status' => 1,
                    ]);

                    break;
                case 2:
                    CheckTransaction::create([
                        'idBA' => $item['bankAccountId'],
                        'idTV' => $item['id'],
                        'number' => strtoupper($item['transactionNumber']),
                        'amount' => $item['totalAmount'],
                        'idUserConfirmed' => Auth::id(),
                        'confirmed_at' => now(),
                        'status' => 1,
                    ]);

                    break;
                case 4:
                    CashTransaction::create([
                        'idTV' => $item['id'],
                        'amount' => $item['totalAmount'],
                        'idUserConfirmed' => Auth::id(),
                        'confirmed_at' => now(),
                        'status' => 1,
                    ]);

                    break;
            }

            $treasuryVoucher->load('userCreated', 'userUpdated');
            event(new TreasuryVoucherEvent($treasuryVoucher, $treasuryVoucher->id, 'update'));
        }

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Comprobantes confirmados exitosamente.',
            ],
            'success' => true,
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
            'idUserVoided' => Auth::id(),
            'voided_at' => now(),
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

    private function calculateTotalAmountCollected(TreasuryVoucher $treasuryVoucher, $tax) {
        $amountCollected = 0;
        $treasuryVoucher->load('voucherToTreasury');

        if ($treasuryVoucher->voucherToTreasury->isEmpty()) {
            $amountCollected = round($treasuryVoucher->amount / (1 + (21 / 100)), 2);
        } else {
            foreach ($treasuryVoucher->voucherToTreasury as $voucher) {
                if (!in_array($voucher->idVoucher, $this->vouchersIds)) {
                    $this->vouchersIds[] = $voucher->idVoucher;
                    $voucherData = Voucher::where('id', $voucher->idVoucher)->first();

                    if ($voucherData) {
                        $amountCollected += round($this->calculateWithoutTaxAmount($voucherData), 2);
                    } else {
                        $amountCollected += round($treasuryVoucher->amount / (1 + ($tax / 100)), 2);
                    }
                }
            }
        }

        return $amountCollected;
    }

    private function calculateWithoutTaxAmount(Voucher $voucher) {
        $amountWithoutTax = 0;
        $voucher->load('items');

        foreach ($voucher->items as $item) {
            if ($item->idVat !== 1) {
                if ($voucher->idType === 1) {
                    $amountWithoutTax -= $item->amount;
                    continue;
                }

                $amountWithoutTax += $item->amount;
            }
        }

        return $amountWithoutTax;
    }

    private function withheldTaxVoucher(TreasuryVoucher $treasuryVoucher, $taxType) {
        $treasuryVoucher->load('voucherToTreasury');
        $data = [
            'amountWithheldTax' => 0,
            'amountWithoutTax' => 0,
        ];

        $voucherIds = $treasuryVoucher->voucherToTreasury->pluck('idVoucher')->toArray();
        $voucheridTVs = $treasuryVoucher->voucherToTreasury->pluck('idTV')->toArray();
        $voucherPaided = VoucherToTreasury::whereIn('idVoucher', $voucherIds)
            ->whereNotIn('idTV', $voucheridTVs)->get();

        $voucherPaidedidTVs = $voucherPaided->pluck('idTV')->toArray();
        $treasuryVoucherWithheldTax = TreasuryVoucherTaxWithholding::whereIn('idOTV', $voucherPaidedidTVs)
            ->where('idTT', $taxType)->get();

        foreach ($treasuryVoucherWithheldTax as $treasuryVoucherWithheldTax) {
            $data['amountWithheldTax'] += $treasuryVoucherWithheldTax->amount;
        }

        if ($treasuryVoucherWithheldTax->count() > 0) {
            $voucherData = Voucher::whereIn('id', $voucherIds)->get();
            foreach ($voucherData as $voucherData) {
                if ($voucherData) {
                    $data['amountWithoutTax'] += round($this->calculateWithoutTaxAmount($voucherData), 2);
                }
            }
        }

        return $data;
    }

    public function exportTreasuryVouchers($type, $status) {
        $voucherType = VoucherType::where('id', $type)->first()->name;
        $treasuryVouchers = TreasuryVoucher::with(['supplier', 'bankAccount', 'bankTransaction', 'cashTransaction', 'checkTransaction', 'userCreated', 'userConfirmed', 'userVoided'])
            ->where('idType', $type)
            ->where('idVS', $status)
            ->get();

        return Excel::download(new TreasuryVouchersExport($treasuryVouchers, $type, $status), 'Comprobantes de tesoreria - ' . $voucherType . '.xlsx');
    }
}

class TreasuryVouchersExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithEvents, WithStyles {
    protected $treasuryVouchers;
    protected $type;
    protected $status;

    public function __construct($treasuryVouchers, $type, $status) {
        $this->treasuryVouchers = $treasuryVouchers;
        $this->type = $type;
        $this->status = $status;
    }

    public function collection() {
        return $this->treasuryVouchers;
    }

    public function headings(): array {
        if ($this->type == 1) {
            return [
                'Cuit',
                'Proveedor',
                'Importe',
                'Estado',
                'Importe',
                'Forma de pago',
                'Banco',
                'Cta. bancaria',
                'N° operación',
                'F. confirmación',
            ];
        }

        return [
            'Cuit',
            'Proveedor',
            'Importe',
            'Estado',
            'Ret. gcias',
            'Ret. suss',
            'Ret. iva',
            'Importe Pagado',
            'Forma de pago',
            'Banco',
            'Cta. bancaria',
            'N° operación',
            'F. pago',
        ];
    }

    public function map($treasuryVoucher): array {
        if ($this->type == 1) {
            return [
                $treasuryVoucher->supplier->cuit,
                strtoupper($treasuryVoucher->supplier->businessName),
                $treasuryVoucher->amount,
                match ($this->status) {
                    '2' => 'CONFIRMADO',
                    '3' => 'ANULADO',
                    default => 'PENDIENTE',
                },
                $this->status === '2' ? $treasuryVoucher->totalAmount : '0',
                $treasuryVoucher->paymentMethod->name ?? '',
                $treasuryVoucher->bankAccount->bank->name ?? '',
                $treasuryVoucher->bankAccount->accountNumber ?? '',
                ($treasuryVoucher->bankTransaction?->number ? $treasuryVoucher->bankTransaction?->number : $treasuryVoucher->checkTransaction?->number) ?? '',
                $treasuryVoucher->paymentDate ? date('d/m/Y', strtotime($treasuryVoucher->paymentDate)) : '00/00/0000',
            ];
        }

        return [
            $treasuryVoucher->supplier->cuit,
            strtoupper($treasuryVoucher->supplier->businessName),
            $treasuryVoucher->amount,
            match ($this->status) {
                '2' => 'CONFIRMADO',
                '3' => 'ANULADO',
                default => 'PENDIENTE',
            },
            $treasuryVoucher->incomeTaxAmount > 0 ? $treasuryVoucher->incomeTaxAmount : '0',
            $treasuryVoucher->socialTaxAmount > 0 ? $treasuryVoucher->socialTaxAmount : '0',
            $treasuryVoucher->vatTaxAmount > 0 ? $treasuryVoucher->vatTaxAmount : '0',
            $this->status === '2' ? $treasuryVoucher->totalAmount : '0',
            $treasuryVoucher->paymentMethod->name ?? '',
            $treasuryVoucher->bankAccount->bank->name ?? '',
            $treasuryVoucher->bankAccount->accountNumber ?? '',
            ($treasuryVoucher->bankTransaction?->number ? $treasuryVoucher->bankTransaction?->number : $treasuryVoucher->checkTransaction?->number) ?? '',
            $treasuryVoucher->paymentDate ? date('d/m/Y', strtotime($treasuryVoucher->paymentDate)) : '00/00/0000',
        ];
    }

    public function styles(Worksheet $sheet) {
        $lastRow = $sheet->getHighestRow();
        return [
            'C2:C' . $lastRow => [
                'numberFormat' => [
                    'formatCode' => '_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)'
                ],
            ],
            'E2:H' . $lastRow => [
                'numberFormat' => [
                    'formatCode' => '_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)'
                ],
            ],
            'M2:M' . $lastRow => [
                'numberFormat' => [
                    'formatCode' => 'dd/mm/yyyy'
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ],
            'K2:K' . $lastRow => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:N1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
            },
        ];
    }
}
