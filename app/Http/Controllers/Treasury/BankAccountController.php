<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\BankAccountRequest;
use App\Http\Resources\Treasury\BankAccountResource;
use App\Models\Treasury\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BankAccountController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:create bank accounts')->only('store');
        $this->middleware('check.permission:edit bank accounts')->only('update');
        $this->middleware('check.permission:view users')->only('info');
    }
    public function store(BankAccountRequest $request) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BankAccountRequest $request, BankAccount $bankAccount) {
        //
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
