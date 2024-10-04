<?php

namespace App\Http\Controllers\Treasury\Supplier;

use App\Events\Treasury\Supplier\SupplierEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Supplier\SupplierRequest;
use App\Http\Resources\Treasury\Supplier\SupplierResource;
use App\Http\Resources\Treasury\Taxes\CategoryResource;
use App\Http\Resources\Treasury\Taxes\VatConditionResource;
use App\Http\Resources\Treasury\Taxes\VatRateResource;
use App\Http\Resources\Treasury\TreasuryVoucher\PayConditionResource;
use App\Http\Resources\Treasury\Voucher\VoucherTypeResource;
use App\Models\Treasury\Supplier\Supplier;
use App\Models\Treasury\Taxes\Category;
use App\Models\Treasury\Taxes\VatCondition;
use App\Models\Treasury\Taxes\VatRate;
use App\Models\Treasury\TreasuryVoucher\PayCondition;
use App\Models\Treasury\Voucher\Voucher;
use App\Models\Treasury\Voucher\VoucherSubtype;
use App\Models\Treasury\Voucher\VoucherType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupplierController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view suppliers')->only(['index', 'exportSuppliers', 'show', 'subtypeRelated', 'invoicePendingToPay']);
        $this->middleware('check.permission:create suppliers')->only('store');
        $this->middleware('check.permission:edit suppliers')->only('update');
        $this->middleware('check.permission:view users')->only('info');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response {
        $suppliers = Supplier::with(['userCreated', 'userUpdated'])->orderBy('name', 'asc')->get();
        $vatCondition = VatCondition::orderBy('name', 'asc')->get();
        $vatRate = VatRate::orderBy('rate', 'asc')->get();
        $category = Category::orderBy('name', 'asc')->get();
        $payCondition = PayCondition::orderBy('name', 'asc')->get();
        $voucherTypes = VoucherType::with(['subtypes'])->orderBy('name', 'asc')->get();

        foreach ($suppliers as $supplier) {
            $vouchers = Voucher::where('idSupplier', $supplier->id)->get();
            $vouchers = $this->calculatePendingToPay($vouchers);
            $supplier->pendingToPay = $vouchers->sum('pendingToPay');
        }

        return Inertia::render('Treasury/Supplier/SuppliersIndex', [
            'suppliers' => SupplierResource::collection($suppliers),
            'vatConditions' => VatConditionResource::collection($vatCondition),
            'vatRates' => VatRateResource::collection($vatRate),
            'categories' => CategoryResource::collection($category),
            'payConditions' => PayConditionResource::collection($payCondition),
            'voucherTypes' => VoucherTypeResource::collection($voucherTypes),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request) {
        $cuit = str_replace('-', '', $request->cuit);
        $SupplierCuit = Supplier::where('cuit', $cuit)->first();

        if ($SupplierCuit) {
            throw ValidationException::withMessages([
                'message' => trans('El proveedor ya se encuentra ingresado.')
            ]);
        }

        $address = $request->address();
        $supplier = Supplier::create([
            'name' => $request->name,
            'businessName' => $request->businessName,
            'cuit' => $cuit,
            'idVC' => $request->idVC,
            'idCat' => $request->idCat,
            'street' => $address->street,
            'streetNumber' => $address->streetNumber,
            'floor' => $address->floor ?? '',
            'apartment' => $address->apartment ?? '',
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'postalCode' => $address->postalCode,
            'osm_id' => $address->osm_id,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
            'phone' => $request->phone ?? '',
            'email' => $request->email ?? '',
            'cbu' => $request->cbu ?? '',
            'incomeTaxWithholding' => $request->incomeTaxWithholding ? 1 : 0,
            'socialTax' => $request->socialTax ? 1 : 0,
            'vatTax' => $request->vatTax ? 1 : 0,
            'idUserCreated' => Auth::id(),
            'created_at' => now(),
            'updated_at' => null,
        ]);

        $supplier->load('userCreated', 'userUpdated');
        event(new SupplierEvent($supplier, $supplier->id, 'create'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Proveedor agregado exitosamente.',
                'supplier' => $supplier,
            ],
            'success' => true,
        ]);
    }

    public function show(string $id) {
        $supplier = Supplier::where('id', $id)->orderBy('id', 'asc')->get();

        return response()->json([
            'supplier' => SupplierResource::collection($supplier),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, Supplier $supplier) {
        $cuit = str_replace('-', '', $request->cuit);
        $supplierCuit = Supplier::where('cuit', $cuit)->whereNot('id', $supplier->id)->first();

        if ($supplierCuit) {
            throw ValidationException::withMessages([
                'message' => trans('El proveedor ya se encuentra ingresado.')
            ]);
        }

        $address = $request->address();
        $supplier->update([
            'name' => $request->name,
            'businessName' => $request->businessName,
            'cuit' => $cuit,
            'idVC' => $request->idVC,
            'idCat' => $request->idCat,
            'street' => $address->street,
            'streetNumber' => $address->streetNumber,
            'floor' => $address->floor ?? '',
            'apartment' => $address->apartment ?? '',
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'postalCode' => $address->postalCode,
            'osm_id' => $address->osm_id,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
            'phone' => $request->phone ?? '',
            'email' => $request->email ?? '',
            'cbu' => $request->cbu ?? '',
            'incomeTaxWithholding' => $request->incomeTaxWithholding ? 1 : 0,
            'socialTax' => $request->socialTax ? 1 : 0,
            'vatTax' => $request->vatTax ? 1 : 0,
            'idUserUpdated' => Auth::id(),
            'updated_at' => now(),
        ]);

        $supplier->load('userCreated', 'userUpdated');
        event(new SupplierEvent($supplier, $supplier->id, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Proveedor modificado exitosamente.',
            ],
            'success' => true,
        ]);
    }

    public function info(Supplier $supplier) {
        $supplier = Supplier::with(['userCreated', 'userUpdated'])->where('id', $supplier->id)->first();

        if (!$supplier) {
            throw ValidationException::withMessages([
                'message' => trans('Proveedor no encontrado.')
            ]);
        }

        return new SupplierResource($supplier);
    }

    public function subtypeRelated(VoucherSubtype $voucherSubtype) {
        $suppliers = $voucherSubtype->suppliers()->orderBy('name', 'asc')->get();

        return response()->json([
            'suppliers' => SupplierResource::collection($suppliers),
        ]);
    }

    private function calculatePendingToPay($vouchers) {
        return $vouchers->map(function ($voucher) {
            $voucher->pendingToPay = $voucher->totalAmount;

            foreach ($voucher->voucherToTreasury as $voucherToTreasury) {
                if ($voucherToTreasury->treasuryVoucher && $voucherToTreasury->treasuryVoucher->idVS != 3) {
                    $voucher->pendingToPay -= $voucherToTreasury->amount;
                    if ($voucher->idType === 1) $voucher->pendingToPay *= -1;
                }
            }

            return $voucher;
        });
    }

    public function invoicePendingToPay() {
        $suppliers = Supplier::with(['userCreated', 'userUpdated'])->orderBy('name', 'asc')->get();
        $countInvoicePendingToPay = 0;

        foreach ($suppliers as $supplier) {
            $vouchers = Voucher::where('idSupplier', $supplier->id)->get();
            $vouchers->each(function ($voucher) use (&$countInvoicePendingToPay) {
                $voucher->pendingToPay = $voucher->totalAmount;

                foreach ($voucher->voucherToTreasury as $voucherToTreasury) {
                    if ($voucherToTreasury->treasuryVoucher && $voucherToTreasury->treasuryVoucher->idVS != 3) {
                        $voucher->pendingToPay -= $voucherToTreasury->amount;
                        if ($voucher->idType === 1) $voucher->pendingToPay *= -1;
                    }
                }

                if ($voucher->pendingToPay > 0) $countInvoicePendingToPay++;
            });
        }

        return response()->json([
            'countInvoicePendingToPay' => $countInvoicePendingToPay,
        ]);
    }

    public function exportSuppliers() {
        $suppliers = Supplier::with(['vatCondition', 'category'])->orderBy('name', 'asc')->get();

        foreach ($suppliers as $supplier) {
            $vouchers = Voucher::where('idSupplier', $supplier->id)->get();
            $vouchers = $this->calculatePendingToPay($vouchers);
            $supplier->pendingToPay = $vouchers->sum('pendingToPay');
        }

        return Excel::download(new SuppliersExport($suppliers), 'Proveedores.xlsx');
    }
}

class SuppliersExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithEvents, WithStyles {
    protected $suppliers;

    public function __construct($suppliers) {
        $this->suppliers = $suppliers;
    }

    public function collection() {
        return $this->suppliers;
    }

    public function headings(): array {
        return [
            'Cuit',
            'Razón Social',
            'Nombre Fantasía',
            'Teléfono',
            'Email',
            'CBU',
            'Cond. IVA',
            'Rubro',
            'Ret. gcias',
            'Ret. suss',
            'Ret. iva',
            'Saldo',
        ];
    }

    public function map($supplier): array {
        return [
            $supplier->cuit,
            strtoupper($supplier->name),
            strtoupper($supplier->businessName),
            $supplier->phone,
            $supplier->email,
            $supplier->cbu,
            $supplier->vatCondition->name,
            $supplier->Category->name,
            $supplier->incomeTaxWithholding ? 'SI' : 'NO',
            $supplier->socialTax ? 'SI' : 'NO',
            $supplier->incomeTaxWithholding ? 'SI' : 'NO',
            $supplier->pendingToPay > 0 ? $supplier->pendingToPay : '0',
        ];
    }

    public function styles(Worksheet $sheet) {
        $lastRow = $sheet->getHighestRow();
        return [
            'L2:L' . $lastRow => [
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
