<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Http\Controllers\Controller;
use App\Http\Resources\Treasury\Voucher\VoucherSubtypeResource;
use App\Http\Resources\Treasury\Voucher\VoucherTypeResource;
use App\Models\Treasury\Voucher\VoucherSubtype;
use App\Models\Treasury\Voucher\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VoucherTypeController extends Controller {
    /**
     * Display a listing of the resource.
     */

    public function __construct() {
        $this->middleware('check.permission:view voucher types')->only('index');
        $this->middleware('check.permission:edit voucher types')->only('update');
    }

    public function index(): Response {
        $voucherTypes = VoucherType::with(['userCreated', 'userUpdated', 'subtypes.userRelated'])->orderBy('name', 'asc')->get();
        $voucherSubtypes = VoucherSubtype::orderBy('name', 'asc')->get();

        return Inertia::render('Treasury/Voucher/VoucherTypesIndex', [
            'voucherTypes' => VoucherTypeResource::collection($voucherTypes),
            'voucherSubtypes' => VoucherSubtypeResource::collection($voucherSubtypes),
        ]);
    }
}
