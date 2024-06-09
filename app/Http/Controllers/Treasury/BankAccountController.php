<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\BankAccountRequest;
use App\Http\Resources\Treasury\BankAccountResource;
use App\Models\Treasury\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class BankAccountController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:create bank accounts')->only('store');
        $this->middleware('check.permission:edit bank accounts')->only('update');
        $this->middleware('check.permission:view users')->only('info');
    }
    public function store(BankAccountRequest $request) {
        $bankAccountNumber = BankAccount::where('accountNumber', $request->accountNumber)
            ->where('idBank', $request->idBank)
            ->where('idAT', $request->idAT)->first();

        if ($bankAccountNumber) {
            throw ValidationException::withMessages([
                'message' => trans('La cuenta bancaria ya se encuentra ingresada.')
            ]);
        }

        $bankAccount = BankAccount::create([
            'idBank' => $request->idBank,
            'idAT' => $request->idAT,
            'accountNumber' => $request->accountNumber,
            'cbu' => $request->cbu,
            'alias' => $request->alias,
            'status' => ($request->status) ? 1 : 0,
            'idUserCreated' => auth()->user()->id,
            'created_at' => now(),
            'updated_at' => null,
        ]);

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Cuenta bancaria agregada exitosamente.',
                'bankAccount' => $bankAccount,
            ],
            'success' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BankAccountRequest $request, BankAccount $bankAccount) {
        $bankAccountNumber = BankAccount::where('accountNumber', $request->accountNumber)
            ->where('idBank', $request->idBank)
            ->where('idAT', $request->idAT)
            ->whereNot('id', $bankAccount->id)->first();

        if ($bankAccountNumber) {
            throw ValidationException::withMessages([
                'message' => trans('La cuenta bancaria ya se encuentra ingresada.')
            ]);
        }

        $bankAccount->update([
            'idAT' => $request->idAT,
            'accountNumber' => $request->accountNumber,
            'cbu' => $request->cbu,
            'alias' => $request->alias,
            'status' => ($request->status) ? 1 : 0,
            'idUserUpdated' => auth()->user()->id,
            'updated_at' => now(),
        ]);

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
        $bankAccount = BankAccount::with(['userCreated', 'userUpdated'])->where('id', $bankAccount->id)->first();

        if (!$bankAccount) {
            throw ValidationException::withMessages([
                'message' => trans('Cuenta bancaria no encontrada.')
            ]);
        }

        return new BankAccountResource($bankAccount);
    }
}
