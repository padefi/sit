<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\BankRequest;
use App\Http\Resources\Treasury\BankAccountResource;
use App\Http\Resources\Treasury\BankResource;
use App\Models\Treasury\Bank;
use App\Models\Treasury\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BankController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view voucher expenses')->only('index');
        $this->middleware('check.permission:create voucher expenses')->only('store');
        $this->middleware('check.permission:edit voucher expenses')->only('update');
        $this->middleware('check.permission:view voucher expenses')->only('info');
    }

    public function index(): Response {
        $banks = Bank::with(['userCreated', 'userUpdated'])->orderBy('name', 'asc')->get();
        $bankAccounts = BankAccount::with(['bank', 'accountType', 'userCreated', 'userUpdated'])->orderBy('accountNumber', 'asc')->get();

        return Inertia::render('Treasury/Bank/BanksIndex', [
            'banks' => BankResource::collection($banks),
            'bankAccounts' => BankAccountResource::collection($bankAccounts),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BankRequest $request) {
        $bankName = Bank::where('name', $request->name)->first();

        if ($bankName) {
            throw ValidationException::withMessages([
                'message' => trans('El banco ya se encuentra ingresado.')
            ]);
        }

        $bank = Bank::create([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'idUserCreated' => auth()->user()->id,
            'created_at' => now(),
            'updated_at' => null,
        ]);

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Banco agregado exitosamente.',
                'bank' => $bank,
            ],
            'success' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BankRequest $request, Bank $bank) {
        $bankName = Bank::where('name', $request->name)->whereNot('id', $bank->id)->first();

        if ($bankName) {
            throw ValidationException::withMessages([
                'message' => trans('El banco ya se encuentra ingresado.')
            ]);
        }

        $bank->update([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'idUserUpdated' => auth()->user()->id,
            'updated_at' => now(),
        ]);

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Banco modificado exitosamente.',
            ],
            'success' => true,
        ]);
    }

    public function info(Bank $bank) {
        $bank = Bank::with(['userCreated', 'userUpdated'])->where('id', $bank->id)->first();

        if (!$bank) {
            throw ValidationException::withMessages([
                'message' => trans('Banco no encontrado.')
            ]);
        }

        return new BankResource($bank);
    }
}
