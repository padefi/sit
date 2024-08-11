<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Events\Treasury\Voucher\VoucherEvent;
use App\Models\Treasury\Voucher\Voucher;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Voucher\VoucherRequest;
use App\Http\Resources\Treasury\Voucher\InvoiceTypeCodeResource;
use App\Http\Resources\Treasury\Voucher\InvoiceTypeResource;
use App\Http\Resources\Treasury\Voucher\VoucherResource;
use App\Models\Treasury\Voucher\InvoiceType;
use App\Models\Treasury\Voucher\VoucherItem;
use App\Models\Treasury\Voucher\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view vouchers')->only('index');
        $this->middleware('check.permission:create vouchers')->only('store');
        $this->middleware('check.permission:edit vouchers')->only('update');
        $this->middleware('check.permission:view users')->only('typesRelated');
    }

    /**
     * Display a listing of the resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(VoucherRequest $request) {
        $VoucherNumber = Voucher::where('idIT', $request->invoiceType)
            ->where('idSupplier', $request->idSupplier)
            ->where('idITCode', $request->invoiceTypeCode)
            ->where('pointOfNumber', $request->pointOfNumber)
            ->where('invoiceNumber', $request->invoiceNumber)
            ->first();

        if ($VoucherNumber) {
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
            'invoicePaymentDate' => date('Y-m-d', strtotime($request->invoicePaymentDate)),
            'idPC' => $request->payCondition,
            'notes' => $request->notes,
            'totalAmount' => $request->totalAmount,
            'idUserCreated' => auth()->user()->id,
            'created_at' => now(),
            'updated_at' => null,
        ]);

        foreach ($request->input('voucherItems', []) as $item) {
            $voucherItem = VoucherItem::create([
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
        $vouchers = Voucher::with(['voucherType', 'voucherSubtype', 'voucherExpense', 'invoiceType', 'invoiceTypeCode', 'payCondition', 'items', 'userCreated', 'userUpdated'])
            ->where('idSupplier', $id)
            ->get();

        return response()->json([
            'vouchers' => VoucherResource::collection($vouchers),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VoucherRequest $request, Voucher $voucher) {
        $VoucherNumber = Voucher::where('idIT', $request->invoiceType)
            ->where('idSupplier', $request->idSupplier)
            ->where('idITCode', $request->invoiceTypeCode)
            ->where('pointOfNumber', $request->pointOfNumber)
            ->where('invoiceNumber', $request->invoiceNumber)
            ->whereNot('id', $voucher->id)
            ->first();

        if ($VoucherNumber) {
            throw ValidationException::withMessages([
                'message' => trans('El comprobante ya se encuentra ingresado.')
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
            'invoicePaymentDate' => date('Y-m-d', strtotime($request->invoicePaymentDate)),
            'idPC' => $request->payCondition,
            'notes' => $request->notes,
            'totalAmount' => $request->totalAmount,
            'idUserUpdated' => auth()->user()->id,
            'updated_at' => now(),
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
}
