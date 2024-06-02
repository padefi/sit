<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\VoucherSubtypeRequest;
use App\Http\Resources\Treasury\VoucherSubtypeResource;
use App\Models\Treasury\VoucherSubtype;
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
    }

    public function index(): Response {
        $voucherSubtypes = VoucherSubtype::with(['userCreated', 'userUpdated'])->get();
        
        return Inertia::render('Treasury/VoucherSubtypes/VoucherSubtypesIndex', [
            'voucherSubtypes' => VoucherSubtypeResource::collection($voucherSubtypes),
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

        $VoucherSubtype = VoucherSubtype::create([
            'name' => $request->name,
            'idUserCreated' => auth()->user()->id,
            'created_at' => now(),
            'status' => ($request->status) ? 1 : 0,
        ]);

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Subtipo agregado exitosamente.',
                'voucherSubtype' => $VoucherSubtype,
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

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Subtipo modificado exitosamente.'
            ],
            'success' => true,
        ]);
    }
}
