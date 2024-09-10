<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Events\Treasury\TreasuryVoucher\TreasuryVoucherEvent;
use App\Events\Treasury\Voucher\VoucherEvent;
use App\Events\Treasury\Voucher\VoucherToTreasuryEvent;
use App\Models\Treasury\Voucher\Voucher;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Voucher\VoucherRequest;
use App\Http\Resources\Treasury\Voucher\InvoiceTypeCodeResource;
use App\Http\Resources\Treasury\Voucher\InvoiceTypeResource;
use App\Http\Resources\Treasury\Voucher\VoucherResource;
use App\Models\Treasury\Voucher\InvoiceType;
use App\Models\Treasury\Voucher\InvoiceTypeCode;
use App\Models\Treasury\TreasuryVoucher\TreasuryVoucher;
use App\Models\Treasury\Voucher\VoucherItem;
use App\Models\Treasury\Voucher\VoucherToTreasury;
use App\Models\Treasury\Voucher\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view vouchers')->only(['index', 'show', 'invoiceTypes', 'typesRelated', 'invoiceTypesRelated']);
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
            'idUserCreated' => auth()->user()->id,
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
            'idUserUpdated' => auth()->user()->id,
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

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Comprobante modificado exitosamente.',
                'voucher' => $voucher,
            ],
            'success' => true,
        ]);
    }

    public function voidVoucher(Voucher $voucher) {
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
            'status' => false,
        ]);

        $voucher->load('userCreated', 'userUpdated', 'items');
        event(new VoucherEvent($voucher, $voucher->id, 'update'));

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
            'idUserCreated' => auth()->user()->id,
            'created_at' => now(),
            'updated_at' => null,
        ]);

        foreach ($request->input('vouchers', []) as $item) {
            VoucherToTreasury::create([
                'idVoucher' => $item['id'],
                'idTV' => $treasuryVoucher->id,
                'amount' => $item['paymentAmount'],
                'idUserSent' => auth()->user()->id,
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
        $voucher = Voucher::with(['userCreated', 'userUpdated'])->where('id', $voucher->id)->first();

        if (!$voucher) {
            throw ValidationException::withMessages([
                'message' => trans('Comprobante no encontrado.')
            ]);
        }

        return new VoucherResource($voucher);
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
}
