<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Events\Treasury\Voucher\VoucherSubtypeEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Voucher\VoucherSubtypeRequest;
use App\Http\Resources\Treasury\Supplier\SupplierResource;
use App\Http\Resources\Treasury\Voucher\VoucherExpenseResource;
use App\Http\Resources\Treasury\Voucher\VoucherSubtypeResource;
use App\Models\Treasury\Supplier\Supplier;
use App\Models\Treasury\Voucher\VoucherExpense;
use App\Models\Treasury\Voucher\VoucherSubtype;
use App\Models\Treasury\Voucher\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VoucherSubtypeController extends Controller {
    /**
     * Display a listing of the resource.
     */

    public function __construct() {
        $this->middleware('check.permission:view voucher subtypes')->only(['index', 'dataRelated']);
        $this->middleware('check.permission:create voucher subtypes')->only('store');
        $this->middleware('check.permission:edit voucher subtypes')->only('update');
        $this->middleware('check.permission:view users')->only('info');
        $this->middleware('check.permission:relationship voucher subtypes')->only(['relate', 'supplierRelate']);
    }

    public function index(): Response {
        $voucherSubtypes = VoucherSubtype::with(['userCreated', 'userUpdated', 'expenses.userRelated', 'suppliers.userRelated'])->orderBy('name', 'asc')->get();
        $voucherExpenses = VoucherExpense::select('id', 'name')->with(['subtypes'])->orderBy('name', 'asc')->get();
        $suppliers = Supplier::select('id', 'businessName')->with(['subtypes'])->orderBy('name', 'asc')->get();

        return Inertia::render('Treasury/Voucher/VoucherSubtypesIndex', [
            'voucherSubtypes' => VoucherSubtypeResource::collection($voucherSubtypes),
            // 'voucherExpenses' => VoucherExpenseResource::collection($voucherExpenses),
            // 'suppliers' => SupplierResource::collection($suppliers),
            'voucherExpenses' => $voucherExpenses,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VoucherSubtypeRequest $request) {
        $voucherSubtype = VoucherSubtype::create([
            'name' => $request->name,
            'idUserCreated' => Auth::id(),
            'created_at' => now(),
            'updated_at' => null,
            'status' => ($request->status) ? 1 : 0,
        ]);

        $tempUUID = $request->keys()[2];
        $voucherSubtype->load('userCreated', 'userUpdated', 'expenses.userRelated');
        event(new VoucherSubtypeEvent($voucherSubtype, $tempUUID, 'create'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Subtipo agregado exitosamente.',
                'voucherSubtype' => $voucherSubtype,
            ],
            'success' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VoucherSubtypeRequest $request, VoucherSubtype $voucherSubtype) {
        $voucherSubtype->update([
            'name' => $request->name,
            'idUserUpdated' => Auth::id(),
            'updated_at' => now(),
            'status' => ($request->status) ? 1 : 0,
        ]);

        $voucherSubtype->load('userCreated', 'userUpdated', 'expenses.userRelated');
        event(new VoucherSubtypeEvent($voucherSubtype, $voucherSubtype->id, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Subtipo modificado exitosamente.',
            ],
            'success' => true,
        ]);
    }

    public function info(VoucherSubtype $voucherSubtype) {
        $voucherSubtype = VoucherSubtype::with(['userCreated', 'userUpdated'])->where('id', $voucherSubtype->id)->first();

        if (!$voucherSubtype) {
            throw ValidationException::withMessages([
                'message' => trans('Subtipo no encontrado.')
            ]);
        }

        return new VoucherSubtypeResource($voucherSubtype);
    }

    public function relate(Request $request, VoucherSubtype $voucherSubtype) {
        $voucherExpense = VoucherExpense::where('id', $request->voucherExpense)->first();

        if (!$voucherExpense) {
            throw ValidationException::withMessages([
                'message' => trans('El gasto no existe.')
            ]);
        }

        $existingRelationship = $voucherSubtype->expenses()->where('id', $voucherExpense->id)->exists();

        if ($existingRelationship) {
            $voucherSubtype->expenses()->detach($voucherExpense->id);
        } else {
            $voucherSubtype->expenses()->attach($voucherExpense->id, [
                'idUserRelated' => Auth::id(),
                'related_at' => now(),
            ]);
        }

        $voucherSubtype->load('userCreated', 'userUpdated', 'expenses.userRelated');
        event(new VoucherSubtypeEvent($voucherSubtype, $voucherSubtype->id, 'relate'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Relación actualizada exitosamente.'
            ],
            'success' => true,
        ]);
    }

    public function supplierRelate(Request $request, VoucherSubtype $voucherSubtype) {
        $supplier = Supplier::where('id', $request->supplier)->first();

        if (!$supplier) {
            throw ValidationException::withMessages([
                'message' => trans('El proveedor no existe.')
            ]);
        }

        $existingRelationship = $voucherSubtype->suppliers()->where('id', $supplier->id)->exists();

        if ($existingRelationship) {
            $voucherSubtype->suppliers()->detach($supplier->id);
        } else {
            $voucherSubtype->suppliers()->attach($supplier->id, [
                'idUserRelated' => Auth::id(),
                'related_at' => now(),
            ]);
        }

        $voucherSubtype->load('userCreated', 'userUpdated', 'suppliers.userRelated');
        event(new VoucherSubtypeEvent($voucherSubtype, $voucherSubtype->id, 'supplierRelate'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Relación actualizada exitosamente.'
            ],
            'success' => true,
        ]);
    }

    public function dataRelated(VoucherType $voucherType) {
        $voucherSubtypes = $voucherType->subtypes()->with('expenses')->where('status', 1)->orderBy('name', 'asc')->get();

        return response()->json([
            'voucherSubtypes' => VoucherSubtypeResource::collection($voucherSubtypes),
        ]);
    }
}
