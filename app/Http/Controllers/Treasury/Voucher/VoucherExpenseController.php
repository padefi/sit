<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Events\Treasury\Voucher\VoucherExpenseEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Voucher\VoucherExpenseRequest;
use App\Http\Resources\Treasury\Voucher\VoucherExpenseResource;
use App\Http\Resources\Users\UserInfoResource;
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
        $voucherExpense = VoucherExpense::with(['userCreated', 'userUpdated'])
            ->select('idUserCreated', 'idUserUpdated', 'created_at', 'updated_at')
            ->where('id', $voucherExpense->id)->first();

        if (!$voucherExpense) {
            throw ValidationException::withMessages([
                'message' => trans('Gasto no encontrado.')
            ]);
        }

        return new UserInfoResource($voucherExpense);
    }

    public function dataRelated(VoucherSubtype $voucherSubtype) {
        $voucherExpenses = $voucherSubtype->expenses()->where('status', 1)->orderBy('name', 'asc')->get();

        return response()->json([
            'voucherExpenses' => VoucherExpenseResource::collection($voucherExpenses),
        ]);
    }
}
