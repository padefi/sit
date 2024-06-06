<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\VoucherExpenseRequest;
use App\Http\Resources\Treasury\VoucherExpenseResource;
use App\Models\Treasury\VoucherExpense;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VoucherExpenseController extends Controller {
    /**
     * Display a listing of the resource.
     */

    public function __construct() {
        $this->middleware('check.permission:view voucher expenses')->only('index');
        $this->middleware('check.permission:create voucher expenses')->only('store');
        $this->middleware('check.permission:edit voucher expenses')->only('update');
        $this->middleware('check.permission:view voucher expenses')->only('info');
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
            'idUserCreated' => auth()->user()->id,
            'created_at' => now(),
            'updated_at' => null,
            'status' => ($request->status) ? 1 : 0,
        ]);

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
            'idUserUpdated' => auth()->user()->id,
            'updated_at' => now(),
            'status' => ($request->status) ? 1 : 0,
        ]);

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
}
