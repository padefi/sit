<?php

namespace App\Http\Controllers\Treasury\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Resources\Treasury\Supplier\SupplierResource;
use App\Http\Resources\Treasury\Taxes\CategoryResource;
use App\Http\Resources\Treasury\Taxes\VatConditionResource;
use App\Models\Treasury\Supplier\Supplier;
use App\Models\Treasury\Taxes\Category;
use App\Models\Treasury\Taxes\VatCondition;
use Illuminate\Http\Request;
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
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier) {
        //
    }
}
