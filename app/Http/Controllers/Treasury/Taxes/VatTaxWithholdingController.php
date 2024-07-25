<?php

namespace App\Http\Controllers\Treasury\Taxes;;

use App\Events\Treasury\Taxes\VatTaxWithholdingEvent;
use App\Models\Treasury\Taxes\VatTaxWithholding;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Taxes\VatTaxWithholdingRequest;
use App\Http\Resources\Treasury\Taxes\CategoryResource;
use App\Http\Resources\Treasury\Taxes\VatTaxWithholdingResource;
use App\Models\Treasury\Taxes\Category;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class VatTaxWithholdingController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view vat tax withholdings')->only('index');
        $this->middleware('check.permission:create vat tax withholdings')->only('store');
        $this->middleware('check.permission:edit vat tax withholdings')->only('update');
        $this->middleware('check.permission:view users')->only('info');
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $category = Category::whereNot('id', 1)->orderBy('name', 'asc')->get();
        $vatTaxWithholding = VatTaxWithholding::with(['category', 'userCreated', 'userUpdated'])->get();

        return response()->json([
            'categories' => CategoryResource::collection($category),
            'vatTaxWithholdings' => VatTaxWithholdingResource::collection($vatTaxWithholding),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VatTaxWithholdingRequest $request) {
        $vatTaxWithholding = VatTaxWithholding::create([
            'idCat' => $request->idCat,
            'rate' => $request->rate,
            'minAmount' => $request->minAmount,
            'fixedAmount' => $request->fixedAmount,
            'startAt' => date('Y-m-d', strtotime($request->startAt)),
            'endAt' => date('Y-m-d', strtotime($request->endAt)),
            'idUserCreated' => auth()->user()->id,
            'created_at' => now(),
            'updated_at' => null,
        ]);

        $tempUUID = $request->keys()[6];
        $vatTaxWithholding->load('category', 'userCreated', 'userUpdated');
        event(new VatTaxWithholdingEvent($vatTaxWithholding, $tempUUID, 'create'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Retención agregada exitosamente.',
                'vatTaxWithholding' => $vatTaxWithholding,
            ],
            'success' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VatTaxWithholdingRequest $request, VatTaxWithholding $vatTaxWithholding) {
        $vatTaxWithholding->update([
            'rate' => $request->rate,
            'minAmount' => $request->minAmount,
            'fixedAmount' => $request->fixedAmount,
            'startAt' => date('Y-m-d', strtotime($request->startAt)),
            'endAt' => date('Y-m-d', strtotime($request->endAt)),
            'idUserUpdated' => auth()->user()->id,
            'updated_at' => now(),
        ]);

        $vatTaxWithholding->load('category', 'userCreated', 'userUpdated');
        event(new VatTaxWithholdingEvent($vatTaxWithholding, $vatTaxWithholding->id, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Retención modificada exitosamente.',
            ],
            'success' => true,
        ]);
    }

    public function info(VatTaxWithholding $vatTaxWithholding) {
        $vatTaxWithholding = VatTaxWithholding::with(['userCreated', 'userUpdated'])->where('id', $vatTaxWithholding->id)->first();

        if (!$vatTaxWithholding) {
            throw ValidationException::withMessages([
                'message' => trans('Retención no encontrada.')
            ]);
        }

        return new VatTaxWithholdingResource($vatTaxWithholding);
    }
}
