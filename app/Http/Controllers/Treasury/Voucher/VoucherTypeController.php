<?php

namespace App\Http\Controllers\Treasury\Voucher;

use App\Events\Treasury\Voucher\VoucherTypeEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Treasury\Voucher\VoucherSubtypeResource;
use App\Http\Resources\Treasury\Voucher\VoucherTypeResource;
use App\Models\Treasury\Voucher\VoucherSubtype;
use App\Models\Treasury\Voucher\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VoucherTypeController extends Controller {
    /**
     * Display a listing of the resource.
     */

    public function __construct() {
        $this->middleware('check.permission:view voucher types')->only(['index', 'data']);
        $this->middleware('check.permission:edit voucher types')->only('update');
        $this->middleware('check.permission:relationship voucher types')->only('relate');
    }

    public function index(): Response {
        $voucherTypes = VoucherType::with(['userCreated', 'userUpdated', 'subtypes.userRelated'])->orderBy('name', 'asc')->get();
        $voucherSubtypes = VoucherSubtype::orderBy('name', 'asc')->get();

        return Inertia::render('Treasury/Voucher/VoucherTypesIndex', [
            'voucherTypes' => VoucherTypeResource::collection($voucherTypes),
            'voucherSubtypes' => VoucherSubtypeResource::collection($voucherSubtypes),
        ]);
    }

    public function relate(Request $request, VoucherType $voucherType) {
        $voucherSubtype = VoucherSubtype::where('id', $request->voucherSubtype)->first();

        if (!$voucherSubtype) {
            throw ValidationException::withMessages([
                'message' => trans('El subtipo no existe.')
            ]);
        }

        $existingRelationship = $voucherType->subtypes()->where('id', $voucherSubtype->id)->exists();

        if ($existingRelationship) {
            $voucherType->subtypes()->detach($voucherSubtype->id);
        } else {
            $voucherType->subtypes()->attach($voucherSubtype->id, [
                'idUserRelated' => Auth::id(),
                'related_at' => now(),
            ]);
        }

        $voucherType->load('userCreated', 'userUpdated', 'subtypes.userRelated');
        event(new VoucherTypeEvent($voucherType, $voucherType->id, 'relate'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Relación actualizada exitosamente.'
            ],
            'success' => true,
        ]);
    }

    public function data() {
        $voucherTypes = VoucherType::orderBy('name', 'asc')->get();

        return response()->json([
            'voucherTypes' => VoucherTypeResource::collection($voucherTypes),
        ]);
    }
}
