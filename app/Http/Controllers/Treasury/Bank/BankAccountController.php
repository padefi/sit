<?php

namespace App\Http\Controllers\Treasury\Bank;

use App\Events\Treasury\Bank\BankAccountEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Bank\BankAccountRequest;
use App\Http\Resources\Treasury\Bank\BankAccountResource;
use App\Http\Resources\Users\UserInfoResource;
use App\Models\Treasury\Bank\BankAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class BankAccountController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:create bank accounts')->only('store');
        $this->middleware('check.permission:edit bank accounts')->only('update');
        $this->middleware('check.permission:view users')->only('info');
    }
    public function store(BankAccountRequest $request) {
        /* $bankAccountNumber = BankAccount::where('accountNumber', $request->accountNumber)
            ->where('idBank', $request->idBank)
            ->where('idAT', $request->idAT)->first();

        if ($bankAccountNumber) {
            throw ValidationException::withMessages([
                'message' => trans('La cuenta bancaria ya se encuentra ingresada.')
            ]);
        } */

        $bankAccount = BankAccount::create([
            'idBank' => $request->idBank,
            'idAT' => $request->idAT,
            'accountNumber' => $request->accountNumber,
            'cbu' => $request->cbu,
            'alias' => $request->alias,
            'status' => $request->status ? 1 : 0,
            'idUserCreated' => Auth::id(),
            'created_at' => now(),
            'updated_at' => null,
        ]);

        $tempUUID = $request->keys()[6];
        $bankAccount->load('bank', 'accountType', 'userCreated', 'userUpdated');
        event(new BankAccountEvent($bankAccount, $tempUUID, 'create'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Cuenta bancaria agregada exitosamente.',
                'bankAccount' => $bankAccount,
            ],
            'success' => true,
        ]);
    }

    public function show(string $id) {
        $bankAccounts = BankAccount::with(['accountType'])
            ->where('idBank', $id)
            ->where('status', 1)
            ->orderBy('accountNumber', 'asc')->get();

        return response()->json([
            'bankAccounts' => BankAccountResource::collection($bankAccounts),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BankAccountRequest $request, BankAccount $bankAccount) {
        /* $bankAccountNumber = BankAccount::where('accountNumber', $request->accountNumber)
            ->where('idBank', $request->idBank)
            ->where('idAT', $request->idAT)
            ->whereNot('id', $bankAccount->id)->first();

        if ($bankAccountNumber) {
            throw ValidationException::withMessages([
                'message' => trans('La cuenta bancaria ya se encuentra ingresada.')
            ]);
        } */

        $bankAccount->update([
            'idAT' => $request->idAT,
            'accountNumber' => $request->accountNumber,
            'cbu' => $request->cbu,
            'alias' => $request->alias,
            'status' => ($request->status) ? 1 : 0,
            'idUserUpdated' => Auth::id(),
            'updated_at' => now(),
        ]);

        $bankAccount->load('bank', 'accountType', 'userCreated', 'userUpdated');
        event(new BankAccountEvent($bankAccount, $bankAccount->id, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Cuenta bancaria modificada exitosamente.',
                'bankAccount' => $bankAccount,
            ],
            'success' => true,
        ]);
    }

    public function info(BankAccount $bankAccount) {
        $bankAccount = BankAccount::with(['userCreated', 'userUpdated'])
            ->select('idUserCreated', 'idUserUpdated', 'created_at', 'updated_at')
            ->where('id', $bankAccount->id)->first();

        if (!$bankAccount) {
            throw ValidationException::withMessages([
                'message' => trans('Cuenta bancaria no encontrada.')
            ]);
        }

        return new UserInfoResource($bankAccount);
    }
}
