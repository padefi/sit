<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Events\Treasury\Voucher\VoucherSubtypeEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Voucher\VoucherSubtypeRequest;
use App\Http\Resources\Treasury\Voucher\VoucherExpenseResource;
use App\Http\Resources\Treasury\Voucher\VoucherSubtypeResource;
use App\Models\Treasury\Voucher\VoucherExpense;
use App\Models\Treasury\Voucher\VoucherSubtype;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VoucherSubtypeController extends Controller {
    /**
     * Display a listing of the resource.
     */

    public function __construct() {
        $this->middleware('check.permission:view voucher subtypes')->only('index');
        $this->middleware('check.permission:create voucher subtypes')->only('store');
        $this->middleware('check.permission:edit voucher subtypes')->only('update');
        $this->middleware('check.permission:view users')->only('info');
    }

    public function index(): Response {
        $voucherSubtypes = VoucherSubtype::with(['userCreated', 'userUpdated', 'expenses.userRelated'])->orderBy('name', 'asc')->get();
        $voucherExpenses = VoucherExpense::orderBy('name', 'asc')->get();
        
        return Inertia::render('Treasury/Voucher/VoucherSubtypesIndex', [
            'voucherSubtypes' => VoucherSubtypeResource::collection($voucherSubtypes),
            'voucherExpenses' => VoucherExpenseResource::collection($voucherExpenses),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VoucherSubtypeRequest $request) {
        $voucherSubtypeName = VoucherSubtype::where('name', $request->name)->first();

        if ($voucherSubtypeName) {
            throw ValidationException::withMessages([
                'message' => trans('El subtipo ya se encuentra ingresado.')
            ]);
        }

        $voucherSubtype = VoucherSubtype::create([
            'name' => $request->name,
            'idUserCreated' => auth()->user()->id,
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
        $voucherSubtypeName = VoucherSubtype::where('name', $request->name)->whereNot('id', $voucherSubtype->id)->first();

        if ($voucherSubtypeName) {
            throw ValidationException::withMessages([
                'message' => trans('El subtipo ya se encuentra ingresado.')
            ]);
        }

        $voucherSubtype->update([
            'name' => $request->name,
            'idUserUpdated' => auth()->user()->id,
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
}
