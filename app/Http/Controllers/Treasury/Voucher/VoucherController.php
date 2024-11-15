<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Events\Treasury\Supplier\SupplierEvent;
use App\Events\Treasury\TreasuryVoucher\TreasuryVoucherEvent;
use App\Events\Treasury\Voucher\VoucherEvent;
use App\Events\Treasury\Voucher\VoucherToTreasuryEvent;
use App\Models\Treasury\Voucher\Voucher;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Treasury\Supplier\SupplierController;
use App\Http\Requests\Treasury\Voucher\VoidedVoucherRequest;
use App\Http\Requests\Treasury\Voucher\VoucherRequest;
use App\Http\Resources\Treasury\Voucher\InvoiceTypeCodeResource;
use App\Http\Resources\Treasury\Voucher\InvoiceTypeResource;
use App\Http\Resources\Treasury\Voucher\VoucherResource;
use App\Http\Resources\Users\UserInfoResource;
use App\Models\Treasury\Supplier\Supplier;
use App\Models\Treasury\Voucher\InvoiceType;
use App\Models\Treasury\Voucher\InvoiceTypeCode;
use App\Models\Treasury\TreasuryVoucher\TreasuryVoucher;
use App\Models\Treasury\Voucher\VoidedVoucher;
use App\Models\Treasury\Voucher\VoucherItem;
use App\Models\Treasury\Voucher\VoucherToTreasury;
use App\Models\Treasury\Voucher\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
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

class VoucherController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view vouchers')->only(['show', 'invoiceTypes', 'typesRelated', 'invoiceTypesRelated', 'showVouchers', 'exportSupplierVouchers']);
        $this->middleware('check.permission:create vouchers')->only('store');
        $this->middleware('check.permission:edit vouchers')->only(['update', 'voidVoucher']);
        $this->middleware('check.permission:view treasury vouchers')->only('vouchersPendingToPay');
        $this->middleware('check.permission:create treasury vouchers')->only('voucherToTreasury');
    }

    /**
     * Display a listing of the resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(VoucherRequest $request) {
        $voucherNumber = Voucher::where('idIT', $request->invoiceType)
            ->where('idSupplier', $request->idSupplier)
            ->where('idITCode', $request->invoiceTypeCode)
            ->where('pointOfNumber', $request->pointOfNumber)
            ->where('invoiceNumber', $request->invoiceNumber)
            ->first();

        if ($voucherNumber) {
            throw ValidationException::withMessages([
                'message' => trans('El comprobante ya se encuentra ingresado.')
            ]);
        }

        $voucher = Voucher::create([
            'idSupplier' => $request->idSupplier,
            'idType' => $request->voucherType,
            'idSubtype' => $request->voucherSubtype,
            'idExpense' => $request->voucherExpense > 0 ? $request->voucherExpense : null,
            'idIT' => $request->invoiceType,
            'idITCode' => $request->invoiceTypeCode,
            'pointOfNumber' => $request->pointOfNumber,
            'invoiceNumber' => $request->invoiceNumber,
            'invoiceDate' => date('Y-m-d', strtotime($request->invoiceDate)),
            'invoiceDueDate' => date('Y-m-d', strtotime($request->invoiceDueDate)),
            'idPC' => $request->payCondition,
            'notes' => $request->notes,
            'totalAmount' => $request->totalAmount,
            'idUserCreated' => Auth::id(),
            'created_at' => now(),
            'updated_at' => null,
        ]);

        foreach ($request->input('voucherItems', []) as $item) {
            VoucherItem::create([
                'idVoucher' => $voucher->id,
                'description' => $item['description'],
                'amount' => $item['amount'],
                'idVat' => $item['vat'],
                'subtotalAmount' => $item['subtotalAmount'],
            ]);
        }

        $voucher->load('userCreated', 'userUpdated', 'items');
        event(new VoucherEvent($voucher, $voucher->id, 'create'));

        $supplier = Supplier::where('id', $voucher->idSupplier)->first();
        $vouchers = Voucher::where('idSupplier', $supplier->id)->get();

        $supplierController = new SupplierController();
        $vouchers = $supplierController->calculatePendingToPay($vouchers);
        $pendingToPay = $vouchers->sum('pendingToPay');

        $supplier->load('userCreated', 'userUpdated');
        event(new SupplierEvent($supplier, $supplier->id, $pendingToPay, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Comprobante cargado exitosamente.',
                'voucher' => $voucher,
            ],
            'success' => true,
        ]);
    }

    public function show(string $id) {
        $vouchers = Voucher::with(['voucherType', 'voucherSubtype', 'voucherExpense', 'invoiceType', 'invoiceTypeCode', 'payCondition', 'items'])
            ->where('id', $id)
            ->get();

        return response()->json([
            'voucher' => VoucherResource::collection($vouchers),
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(VoucherRequest $request, Voucher $voucher) {
        $voucherNumber = Voucher::where('idIT', $request->invoiceType)
            ->where('idSupplier', $request->idSupplier)
            ->where('idITCode', $request->invoiceTypeCode)
            ->where('pointOfNumber', $request->pointOfNumber)
            ->where('invoiceNumber', $request->invoiceNumber)
            ->whereNot('id', $voucher->id)
            ->first();

        if ($voucherNumber) {
            throw ValidationException::withMessages([
                'message' => trans('El comprobante ya se encuentra ingresado.')
            ]);
        }

        $voucherExist = Voucher::where('id', $voucher->id)->first();

        if (!$voucherExist) {
            throw ValidationException::withMessages([
                'message' => trans('Comprobante no encontrado.')
            ]);
        }

        $treasuryVoucher = VoucherToTreasury::where('idVoucher', $voucher->id)
            ->whereHas('treasuryVoucher', function ($query) {
                $query->where('idVS', '!=', 3);
            })
            ->first();

        if ($treasuryVoucher) {
            throw ValidationException::withMessages([
                'message' => trans('El comprobante ya ha sido enviado a la tesorería.')
            ]);
        }

        $voucher->update([
            'idType' => $request->voucherType,
            'idSubtype' => $request->voucherSubtype,
            'idExpense' => $request->voucherExpense > 0 ? $request->voucherExpense : null,
            'idIT' => $request->invoiceType,
            'idITCode' => $request->invoiceTypeCode,
            'pointOfNumber' => $request->pointOfNumber,
            'invoiceNumber' => $request->invoiceNumber,
            'invoiceDate' => date('Y-m-d', strtotime($request->invoiceDate)),
            'invoiceDueDate' => date('Y-m-d', strtotime($request->invoiceDueDate)),
            'idPC' => $request->payCondition,
            'notes' => $request->notes,
            'totalAmount' => $request->totalAmount,
            'idUserUpdated' => Auth::id(),
            'updated_at' => now(),
            'status' => true,
        ]);

        /* Deleting items */
        $itemIds = array_column($request->input('voucherItems', []), 'id');
        $existingItems = VoucherItem::where('idVoucher', $voucher->id)->get();
        $existingItemIds = $existingItems->pluck('id')->toArray();

        $idsToDelete = array_diff($existingItemIds, $itemIds);
        if (!empty($idsToDelete)) {
            VoucherItem::whereIn('id', $idsToDelete)->delete();
        }
        /* Deleting items */

        foreach ($request->input('voucherItems', []) as $item) {
            $voucherItem = VoucherItem::where('id', $item['id'])
                ->where('idVoucher', $voucher->id)
                ->first();

            if ($voucherItem) {
                $voucherItem->update([
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                    'idVat' => $item['vat'],
                    'subtotalAmount' => $item['subtotalAmount'],
                ]);
            } else {
                $voucherItem = VoucherItem::create([
                    'idVoucher' => $voucher->id,
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                    'idVat' => $item['vat'],
                    'subtotalAmount' => $item['subtotalAmount'],
                ]);
            }
        }

        $voucher->load('userCreated', 'userUpdated', 'items');
        event(new VoucherEvent($voucher, $voucher->id, 'update'));

        $supplier = Supplier::where('id', $voucher->idSupplier)->first();
        $vouchers = Voucher::where('idSupplier', $supplier->id)->get();

        $supplierController = new SupplierController();
        $vouchers = $supplierController->calculatePendingToPay($vouchers);
        $pendingToPay = $vouchers->sum('pendingToPay');

        $supplier->load('userCreated', 'userUpdated');
        event(new SupplierEvent($supplier, $supplier->id, $pendingToPay, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Comprobante modificado exitosamente.',
                'voucher' => $voucher,
            ],
            'success' => true,
        ]);
    }

    public function voidVoucher(VoidedVoucherRequest $request, Voucher $voucher) {
        $treasuryVoucher = VoucherToTreasury::where('idVoucher', $voucher->id)
            ->whereHas('treasuryVoucher', function ($query) {
                $query->where('idVS', '!=', 3);
            })
            ->first();

        if ($treasuryVoucher) {
            throw ValidationException::withMessages([
                'message' => trans('El comprobante ya ha sido enviado a la tesorería.')
            ]);
        }

        VoidedVoucher::create([
            'idVoucher' => $voucher->id,
            'notes' => strtoupper($request->notes),
            'idUserVoided' => Auth::id(),
            'voided_at' => now(),
        ]);

        $voucher->update([
            'idUserVoided' => Auth::id(),
            'voided_at' => now(),
            'status' => false,
        ]);

        $voucher->load('userCreated', 'userUpdated', 'items');
        event(new VoucherEvent($voucher, $voucher->id, 'update'));

        $supplier = Supplier::where('id', $voucher->idSupplier)->first();
        $vouchers = Voucher::where('idSupplier', $supplier->id)->get();

        $supplierController = new SupplierController();
        $vouchers = $supplierController->calculatePendingToPay($vouchers);
        $pendingToPay = $vouchers->sum('pendingToPay');

        $supplier->load('userCreated', 'userUpdated');
        event(new SupplierEvent($supplier, $supplier->id, $pendingToPay, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Comprobante aunlado exitosamente.',
                'voucher' => $voucher,
            ],
            'success' => true,
        ]);
    }

    public function showVouchers(string $id) {
        $vouchers = Voucher::with(['voucherType', 'voucherSubtype', 'voucherExpense', 'invoiceType', 'invoiceTypeCode', 'payCondition', 'items', 'userCreated', 'userUpdated'])
            ->where('idSupplier', $id)
            ->get();
        $vouchers = $this->calculatePendingToPay($vouchers);

        return response()->json([
            'vouchers' => VoucherResource::collection($vouchers),
        ]);
    }

    public function invoiceTypes() {
        $invoiceTypes = InvoiceType::orderBy('id', 'asc')->get();
        $invoiceTypeCodes = InvoiceTypeCode::orderBy('id', 'asc')->get();

        return response()->json([
            'invoiceTypes' => InvoiceTypeResource::collection($invoiceTypes),
            'invoiceTypeCodes' => InvoiceTypeCodeResource::collection($invoiceTypeCodes),
        ]);
    }

    public function voucherToTreasury(Request $request) {
        $totalAmountTreasuryVoucher = 0;

        foreach ($request->input('vouchers', []) as $item) {
            $voucher = Voucher::where('id', $item['id'])
                ->where('idSupplier', $item['idSupplier'])
                ->with('voucherToTreasury.treasuryVoucher')
                ->first();

            if (!$voucher) {
                throw ValidationException::withMessages([
                    'message' => trans('Comprobante no encontrado.')
                ]);
            }

            /* if ($item['paymentAmount'] > $voucher->totalAmount) {
                throw ValidationException::withMessages([
                    'message' => trans('El importe a pagar es mayor a la deuda total del comprobante.')
                ]);
            } */

            /* $amountTreasuryVoucher = 0;
            foreach ($voucher->voucherToTreasury as $voucherToTreasury) {
                if ($voucherToTreasury->treasuryVoucher && $voucherToTreasury->treasuryVoucher->idVS != 3) {
                    $amountTreasuryVoucher += $voucherToTreasury->treasuryVoucher->amount;
                }
            } */

            $voucher = $this->calculatePendingToPay(collect([$voucher]))->first();

            if ($item['paymentAmount'] > $voucher->pendingToPay) {
                throw ValidationException::withMessages([
                    'message' => trans('El importe a pagar es mayor al saldo pendiente.')
                ]);
            }

            $totalAmountTreasuryVoucher = $item['idType'] == 2 ? $totalAmountTreasuryVoucher + $item['paymentAmount'] : $totalAmountTreasuryVoucher - $item['paymentAmount'];
        }

        $idType = $totalAmountTreasuryVoucher >= 0 ? 2 : 1;
        $amount = $totalAmountTreasuryVoucher >= 0 ? $totalAmountTreasuryVoucher : $totalAmountTreasuryVoucher * -1;

        $treasuryVoucher = TreasuryVoucher::create([
            'idType' => $idType,
            'idSupplier' => $voucher->idSupplier,
            'idVS' => 1,
            'amount' => $amount,
            'totalAmount' => $amount,
            'idUserCreated' => Auth::id(),
            'created_at' => now(),
            'updated_at' => null,
        ]);

        foreach ($request->input('vouchers', []) as $item) {
            VoucherToTreasury::create([
                'idVoucher' => $item['id'],
                'idTV' => $treasuryVoucher->id,
                'amount' => $item['paymentAmount'],
                'idUserSent' => Auth::id(),
                'related_at' => now(),
            ]);
        }

        $treasuryVoucher->load('userCreated', 'userUpdated');
        event(new VoucherToTreasuryEvent($treasuryVoucher, $treasuryVoucher->id, 'create'));
        event(new TreasuryVoucherEvent($treasuryVoucher, $treasuryVoucher->id, 'create'));
        event(new VoucherEvent($voucher, $voucher->id, 'voucherToTreasury'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Comprobantes enviados a tesorería correctamente.',
                'treasuryVoucher' => $treasuryVoucher,
            ],
            'success' => true,
        ]);
    }

    public function vouchersPendingToPay(string $id) {
        $vouchers = Voucher::with(['voucherType', 'voucherSubtype', 'voucherExpense', 'invoiceType', 'invoiceTypeCode', 'payCondition', 'items', 'userCreated', 'userUpdated'])
            ->where('idSupplier', $id)
            ->where('status', true)
            ->with('voucherToTreasury.treasuryVoucher')
            ->get();

        $vouchers = $this->calculatePendingToPay($vouchers);
        $filteredVouchers = $this->filterPendingToPay($vouchers);

        return response()->json([
            'vouchers' => VoucherResource::collection($filteredVouchers),
        ]);
    }

    public function typesRelated(VoucherType $voucherType) {
        $invoiceTypes = $voucherType->invoiceTypes()->orderBy('id', 'asc')->get();

        return response()->json([
            'invoiceTypes' => InvoiceTypeResource::collection($invoiceTypes),
        ]);
    }

    public function invoiceTypesRelated(InvoiceType $invoiceType) {
        $invoiceTypeCodes = $invoiceType->invoiceTypeCodes()->orderBy('id', 'asc')->get();

        return response()->json([
            'invoiceTypeCodes' => InvoiceTypeCodeResource::collection($invoiceTypeCodes),
        ]);
    }

    public function info(Voucher $voucher) {
        $voucher = Voucher::with(['invoiceType', 'invoiceTypeCode', 'userCreated', 'userUpdated', 'userVoided'])
            ->select('idIT', 'idITCode', 'pointOfNumber', 'invoiceNumber', 'idUserCreated', 'idUserUpdated', 'idUserVoided', 'created_at', 'updated_at', 'voided_at')
            ->where('id', $voucher->id)->first();

        if (!$voucher) {
            throw ValidationException::withMessages([
                'message' => trans('Comprobante no encontrado.')
            ]);
        }

        // return new UserInfoResource($voucher);
        return response()->json([
            'userData' => new UserInfoResource($voucher),
            'voucherData' => [
                'invoiceType' => $voucher->invoiceType->name,
                'invoiceTypeCode' => $voucher->invoiceTypeCode->name,
                'pointOfNumber' => $voucher->pointOfNumber,
                'invoiceNumber' => $voucher->invoiceNumber,
            ],
        ]);
    }

    private function calculatePendingToPay($vouchers) {
        return $vouchers->map(function ($voucher) {
            $amountTreasuryVoucher = 0;

            foreach ($voucher->voucherToTreasury as $voucherToTreasury) {
                if ($voucherToTreasury->treasuryVoucher && $voucherToTreasury->treasuryVoucher->idVS != 3) {
                    $amountTreasuryVoucher += $voucherToTreasury->amount;
                }
            }

            $voucher->pendingToPay = $voucher->totalAmount - $amountTreasuryVoucher;

            return $voucher;
        });
    }

    private function filterPendingToPay($vouchers) {
        return $vouchers->filter(function ($voucher) {
            return $voucher->pendingToPay > 0;
        });
    }

    public function exportSupplierVouchers(string $id) {
        $supplier = Supplier::where('id', $id)->first();

        if (!$supplier) {
            throw ValidationException::withMessages([
                'message' => trans('Proveedor no encontrado.')
            ]);
        }

        $vouchers = Voucher::with(['voucherType', 'voucherSubtype', 'voucherExpense', 'invoiceType', 'invoiceTypeCode', 'payCondition', 'items', 'userCreated', 'userUpdated'])
            ->where('idSupplier', $id)
            ->get();
        $vouchers = $this->calculatePendingToPay($vouchers);

        return Excel::download(new SupplierVouchersExport($vouchers), 'Comprobantes de ' . strtoupper($supplier->businessName) . '.xlsx');
    }
}

class SupplierVouchersExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithEvents, WithStyles {
    protected $vouchers;

    public function __construct($vouchers) {
        $this->vouchers = $vouchers;
    }

    public function collection() {
        return $this->vouchers;
    }

    public function headings(): array {
        return [
            'T. Comp',
            'T. Fac',
            'Número',
            'Fecha',
            'Vencimiento',
            'Cond. Pago',
            'Importe',
            'Estado',
            'Saldo',
        ];
    }

    public function map($voucher): array {
        return [
            strtoupper($voucher->invoiceType->name),
            strtoupper($voucher->InvoiceTypeCode->name),
            str_pad($voucher->pointOfNumber, 5, '0', STR_PAD_LEFT) . '-' . str_pad($voucher->invoiceNumber, 8, '0', STR_PAD_LEFT),
            date('d/m/Y', strtotime($voucher->invoiceDate)),
            date('d/m/Y', strtotime($voucher->invoiceDueDate)),
            strtoupper($voucher->payCondition->name),
            $voucher->totalAmount,
            $voucher->status == 1 ? ($voucher->pendingToPay > 0 ? 'PENDIENTE' : 'FINALIZADO') : 'ANULADO',
            $voucher->pendingToPay > 0 ? $voucher->pendingToPay : '0',
        ];
    }

    public function styles(Worksheet $sheet) {
        $lastRow = $sheet->getHighestRow();
        return [
            'D2:E' . $lastRow => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ],
            'G2:G' . $lastRow => [
                'numberFormat' => [
                    'formatCode' => '_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)'
                ],
            ],
            'I2:I' . $lastRow => [
                'numberFormat' => [
                    'formatCode' => '_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)'
                ],
            ],
        ];
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:L1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
            },
        ];
    }
}
