<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\VoucherSubtypeRequest;
use App\Http\Resources\Treasury\VoucherSubtypesResource;
use App\Models\Treasury\VoucherSubtypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VoucherSubtypesController extends Controller {
    /**
     * Display a listing of the resource.
     */

    public function __construct() {
        $this->middleware('check.permission:view voucher subtypes')->only('index');
        $this->middleware('check.permission:create voucher subtypes')->only('store');
        $this->middleware('check.permission:edit voucher subtypes')->only('update');
    }

    public function index(): Response {
        $voucherSubtypes = VoucherSubtypes::all();
        
        return Inertia::render('Treasury/VoucherSubtypes/VoucherSubtypesIndex', [
            'voucherSubtypes' => VoucherSubtypesResource::collection($voucherSubtypes),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VoucherSubtypeRequest $request) {
        $voucherSubtypesName = VoucherSubtypes::where('name', $request->name)->first();

        if ($voucherSubtypesName) {
            throw ValidationException::withMessages([
                'message' => trans('El subtipo ya se encuentra ingresado.')
            ]);
        }

        $VoucherSubtypes = VoucherSubtypes::create([
            'name' => $request->name,
            'idUserCreated' => auth()->user()->id,
            'created_at' => now(),
            'status' => ($request->is_active) ? 1 : 0,
        ]);

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Subtipo agregado exitosamente.',
                'voucherSubtypes' => $VoucherSubtypes,
            ],
            'success' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VoucherSubtypes $voucherSubtypes) {
        //
    }
}
