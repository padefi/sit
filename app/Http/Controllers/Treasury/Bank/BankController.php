<?php

namespace App\Http\Controllers\Treasury\Bank;

use App\Events\Treasury\Bank\BankEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Bank\BankRequest;
use App\Http\Resources\Treasury\Bank\BankAccountResource;
use App\Http\Resources\Treasury\Bank\BankAccountTypeResource;
use App\Http\Resources\Treasury\Bank\BankResource;
use App\Models\Treasury\Bank\Bank;
use App\Models\Treasury\Bank\BankAccount;
use App\Models\Treasury\Bank\BankAccountType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BankController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view banks')->only('index');
        $this->middleware('check.permission:create banks')->only('store');
        $this->middleware('check.permission:edit banks')->only('update');
        $this->middleware('check.permission:view users')->only('info');
    }

    public function index(): Response {
        $banks = Bank::with(['userCreated', 'userUpdated'])->orderBy('name', 'asc')->get();
        $bankAccounts = BankAccount::with(['bank', 'accountType', 'userCreated', 'userUpdated'])->orderBy('accountNumber', 'asc')->get();
        $bankAccountType = BankAccountType::orderBy('name', 'asc')->get();

        return Inertia::render('Treasury/Bank/BanksIndex', [
            'banks' => BankResource::collection($banks),
            'bankAccounts' => BankAccountResource::collection($bankAccounts),
            'bankAccountTypes' => BankAccountTypeResource::collection($bankAccountType),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BankRequest $request) {
        $address = $request->address();
        $bank = Bank::create([
            'name' => $request->name,
            'street' => $address->street,
            'streetNumber' => $address->streetNumber,
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'postalCode' => $address->postalCode,
            'osm_id' => $address->osm_id,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
            'phone' => $request->phone,
            'email' => $request->email,
            'idUserCreated' => Auth::id(),
            'created_at' => now(),
            'updated_at' => null,
        ]);

        $tempUUID = $request->keys()[4];
        $bank->load('userCreated', 'userUpdated');
        event(new BankEvent($bank, $tempUUID, 'create'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Banco agregado exitosamente.',
                'bank' => $bank,
            ],
            'success' => true,
        ]);
    }

    public function showBanks() {
        $banks = Bank::orderBy('name', 'asc')->get();

        return response()->json([
            'banks' => BankResource::collection($banks),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BankRequest $request, Bank $bank) {
        $address = $request->address();
        $bank->update([
            'name' => $request->name,
            'street' => $address->street,
            'streetNumber' => $address->streetNumber,
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'postalCode' => $address->postalCode,
            'osm_id' => $address->osm_id,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
            'phone' => $request->phone,
            'email' => $request->email,
            'idUserUpdated' => Auth::id(),
            'updated_at' => now(),
        ]);

        $bank->load('userCreated', 'userUpdated');
        event(new BankEvent($bank, $bank->id, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Banco modificado exitosamente.',
                'bank' => $bank,
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
