<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Events\Treasury\Voucher\VoucherExpenseEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Voucher\VoucherExpenseRequest;
use App\Http\Resources\Treasury\Voucher\VoucherExpenseResource;
use App\Models\Treasury\Voucher\VoucherExpense;
use App\Models\Treasury\Voucher\VoucherSubtype;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VoucherExpenseController extends Controller {
    /**
     * Display a listing of the resource.
     */

    public function __construct() {
        $this->middleware('check.permission:view voucher expenses')->only(['index', 'dataRelated']);
        $this->middleware('check.permission:create voucher expenses')->only('store');
        $this->middleware('check.permission:edit voucher expenses')->only('update');
        $this->middleware('check.permission:view users')->only('info');
    }

    public function index(): Response {
        $voucherExpenses = VoucherExpense::with(['userCreated', 'userUpdated'])->orderBy('name', 'asc')->get();
        
        return Inertia::render('Treasury/Voucher/VoucherExpensesIndex', [
            'voucherExpenses' => VoucherExpenseResource::collection($voucherExpenses),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VoucherExpenseRequest $request) {
        $voucherExpenseName = VoucherExpense::where('name', $request->name)->first();

        if ($voucherExpenseName) {
            throw ValidationException::withMessages([
                'message' => trans('El subtipo ya se encuentra ingresado.')
            ]);
        }

        $voucherExpense = VoucherExpense::create([
            'name' => $request->name,
            'idUserCreated' => Auth::id(),
            'created_at' => now(),
            'updated_at' => null,
            'status' => ($request->status) ? 1 : 0,
        ]);

        $tempUUID = $request->keys()[2];
        $voucherExpense->load('userCreated', 'userUpdated', 'subtypes.userRelated');
        event(new VoucherExpenseEvent($voucherExpense, $tempUUID, 'create'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Gasto agregado exitosamente.',
                'voucherExpense' => $voucherExpense,
            ],
            'success' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VoucherExpenseRequest $request, VoucherExpense $voucherExpense) {
        $voucherExpenseName = VoucherExpense::where('name', $request->name)->whereNot('id', $voucherExpense->id)->first();

        if ($voucherExpenseName) {
            throw ValidationException::withMessages([
                'message' => trans('El gasto ya se encuentra ingresado.')
            ]);
        }

        $voucherExpense->update([
            'name' => $request->name,
            'idUserUpdated' => Auth::id(),
            'updated_at' => now(),
            'status' => ($request->status) ? 1 : 0,
        ]);

        $voucherExpense->load('userCreated', 'userUpdated', 'subtypes.userRelated');
        event(new VoucherExpenseEvent($voucherExpense, $voucherExpense->id, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Gasto modificado exitosamente.',
            ],
            'success' => true,
        ]);
    }

    public function info(VoucherExpense $voucherExpense) {
        $voucherExpense = VoucherExpense::with(['userCreated', 'userUpdated'])->where('id', $voucherExpense->id)->first();

        if (!$voucherExpense) {
            throw ValidationException::withMessages([
                'message' => trans('Gasto no encontrado.')
            ]);
        }

        return new VoucherExpenseResource($voucherExpense);
    }

    public function dataRelated(VoucherSubtype $voucherSubtype) {
        $voucherExpenses = $voucherSubtype->expenses()->where('status', 1)->orderBy('name', 'asc')->get();

        return response()->json([
            'voucherExpenses' => VoucherExpenseResource::collection($voucherExpenses),
        ]);
    }
}
