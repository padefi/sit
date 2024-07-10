<?php

namespace App\Http\Controllers\Treasury\Supplier;

use App\Events\Treasury\Supplier\SupplierEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Supplier\SupplierRequest;
use App\Http\Resources\Treasury\Supplier\SupplierResource;
use App\Http\Resources\Treasury\Taxes\CategoryResource;
use App\Http\Resources\Treasury\Taxes\VatConditionResource;
use App\Models\Treasury\Supplier\Supplier;
use App\Models\Treasury\Taxes\Category;
use App\Models\Treasury\Taxes\VatCondition;
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

    public function index(): Response {
        $suppliers = Supplier::with(['userCreated', 'userUpdated'])->orderBy('name', 'asc')->get();
        $vatCondition = VatCondition::orderBy('name', 'asc')->get();
        $category = Category::orderBy('name', 'asc')->get();

        return Inertia::render('Treasury/Supplier/SuppliersIndex', [
            'suppliers' => SupplierResource::collection($suppliers),
            'vatConditions' => VatConditionResource::collection($vatCondition),
            'categories' => CategoryResource::collection($category),
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

        $supplier = Supplier::create([
            'name' => $request->name,
            'businessName' => $request->businessName,
            'cuit' => $cuit,
            'idVC' => $request->idVC,
            'idCat' => $request->idCat,
            'street' => $request->address->street,
            'streetNumber' => $request->address->streetNumber,
            'floor' => $request->address->floor,
            'apartment' => $request->address->apartment,
            'city' => $request->address->city,
            'state' => $request->address->state,
            'postalCode' => $request->address->postalCode,
            'osm_id' => $request->address->osm_id,
            'phone' => $request->phone,
            'email' => $request->email,
            'cbu' => $request->cbu,
            'incomeTax' => $request->incomeTax ? 1 : 0,
            'socialTax' => $request->socialTax ? 1 : 0,
            'idUserCreated' => auth()->user()->id,
            'created_at' => now(),
            'updated_at' => null,
        ]);

        $supplier->load('supplierCreated', 'supplierUpdated');
        event(new SupplierEvent($supplier, $supplier->id, 'create'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Proveedor agregado exitosamente.',
                'bank' => $supplier,
            ],
            'success' => true,
        ]);
    }
}
