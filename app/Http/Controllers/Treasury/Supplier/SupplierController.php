<?php

namespace App\Http\Controllers\Treasury\Supplier;

use App\Events\Treasury\Supplier\SupplierEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Supplier\SupplierRequest;
use App\Http\Resources\Treasury\Supplier\SupplierResource;
use App\Http\Resources\Treasury\Taxes\CategoryResource;
use App\Http\Resources\Treasury\Taxes\VatConditionResource;
use App\Http\Resources\Treasury\Voucher\InvoiceTypeCodeResource;
use App\Http\Resources\Treasury\Voucher\InvoiceTypeResource;
use App\Models\Treasury\Supplier\Supplier;
use App\Models\Treasury\Taxes\Category;
use App\Models\Treasury\Taxes\VatCondition;
use App\Models\Treasury\Voucher\InvoiceType;
use App\Models\Treasury\Voucher\InvoiceTypeCode;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view suppliers')->only('index');
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
        $category = Category::orderBy('name', 'asc')->get();
        $invoiceType = InvoiceType::orderBy('name', 'asc')->get();
        $invoiceTypeCode = InvoiceTypeCode::orderBy('name', 'asc')->get();

        return Inertia::render('Treasury/Supplier/SuppliersIndex', [
            'suppliers' => SupplierResource::collection($suppliers),
            'vatConditions' => VatConditionResource::collection($vatCondition),
            'categories' => CategoryResource::collection($category),
            'invoiceTypes' => InvoiceTypeResource::collection($invoiceType),
            'invoiceTypeCodes' => InvoiceTypeCodeResource::collection($invoiceTypeCode),
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
            'idUserCreated' => auth()->user()->id,
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

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, Supplier $supplier) {
        $cuit = str_replace('-', '', $request->cuit);
        $supplierCuit = Supplier::where('cuit', $cuit)->whereNot('id', $supplier->id)->first();

        if ($supplierCuit) {
            throw ValidationException::withMessages([
                'message' => trans('El banco ya se encuentra ingresado.')
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
            'idUserUpdated' => auth()->user()->id,
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
}
