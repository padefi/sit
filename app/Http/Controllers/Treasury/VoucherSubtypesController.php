<?php

namespace App\Http\Controllers\Treasury;

use App\Http\Controllers\Controller;
use App\Http\Resources\Treasury\VoucherSubtypesResource;
use App\Models\Treasury\VoucherSubtypes;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VoucherSubtypesController extends Controller {
    /**
     * Display a listing of the resource.
     */

    public function __construct() {
        $this->middleware('check.permission:view voucher subtypes')->only('index');
        $this->middleware('check.permission:create voucher subtypes')->only('store');
        $this->middleware('check.permission:edit voucher subtypes')->only('update');
    }

    public function index() {

        $voucherSubtypes = VoucherSubtypes::all();
        
        return Inertia::render('Treasury/VoucherSubtypes/VoucherSubtypesIndex', [
            'voucherSubtypes' => VoucherSubtypesResource::collection($voucherSubtypes),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(VoucherSubtypes $voucherSubtypes) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VoucherSubtypes $voucherSubtypes) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VoucherSubtypes $voucherSubtypes) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VoucherSubtypes $voucherSubtypes) {
        //
    }
}
