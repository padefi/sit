<?php

namespace App\Http\Controllers\Treasury\Taxes;

use App\Events\Treasury\Taxes\IncomeTaxWithholdingEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Taxes\IncomeTaxWithholdingRequest;
use App\Http\Resources\Treasury\Taxes\CategoryResource;
use App\Http\Resources\Treasury\Taxes\IncomeTaxWithholdingResource;
use App\Http\Resources\Treasury\Taxes\IncomeTaxWithholdingScaleResource;
use App\Models\Treasury\Taxes\Category;
use App\Models\Treasury\Taxes\IncomeTaxWithholding;
use App\Models\Treasury\Taxes\IncomeTaxWithholdingScale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class IncomeTaxWithholdingController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view income tax withholdings')->only('index');
        $this->middleware('check.permission:create income tax withholdings')->only('store');
        $this->middleware('check.permission:edit income tax withholdings')->only('update');
        $this->middleware('check.permission:view users')->only('info');
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $category = Category::whereNot('id', 1)->orderBy('name', 'asc')->get();
        $incomeTaxWithholding = IncomeTaxWithholding::with(['category', 'userCreated', 'userUpdated'])->get();
        $incomeTaxWithholdingScale = IncomeTaxWithholdingScale::with(['category', 'userCreated', 'userUpdated'])->get();

        return response()->json([
            'categories' => CategoryResource::collection($category),
            'incomeTaxWithholdings' => IncomeTaxWithholdingResource::collection($incomeTaxWithholding),
            'incomeTaxWithholdingScales' => IncomeTaxWithholdingScaleResource::collection($incomeTaxWithholdingScale),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncomeTaxWithholdingRequest $request) {
        $incomeTaxWithholding = IncomeTaxWithholding::create([
            'idCat' => $request->idCat,
            'rate' => $request->rate,
            'minAmount' => $request->minAmount,
            'fixedAmount' => $request->fixedAmount,
            'startAt' => date('Y-m-d', strtotime($request->startAt)),
            'endAt' => date('Y-m-d', strtotime($request->endAt)),
            'idUserCreated' => Auth::id(),
            'created_at' => now(),
            'updated_at' => null,
        ]);

        $tempUUID = $request->keys()[6];
        $incomeTaxWithholding->load('category', 'userCreated', 'userUpdated');
        event(new IncomeTaxWithholdingEvent($incomeTaxWithholding, $tempUUID, 'create'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Retención agregada exitosamente.',
                'incomeTaxWithholding' => $incomeTaxWithholding,
            ],
            'success' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IncomeTaxWithholdingRequest $request, IncomeTaxWithholding $incomeTaxWithholding) {
        $incomeTaxWithholding->update([
            'rate' => $request->rate,
            'minAmount' => $request->minAmount,
            'fixedAmount' => $request->fixedAmount,
            'startAt' => date('Y-m-d', strtotime($request->startAt)),
            'endAt' => date('Y-m-d', strtotime($request->endAt)),
            'idUserUpdated' => Auth::id(),
            'updated_at' => now(),
        ]);

        $incomeTaxWithholding->load('category', 'userCreated', 'userUpdated');
        event(new IncomeTaxWithholdingEvent($incomeTaxWithholding, $incomeTaxWithholding->id, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Retención modificada exitosamente.',
            ],
            'success' => true,
        ]);
    }

    public function info(IncomeTaxWithholding $incomeTaxWithholding) {
        $incomeTaxWithholding = IncomeTaxWithholding::with(['userCreated', 'userUpdated'])->where('id', $incomeTaxWithholding->id)->first();

        if (!$incomeTaxWithholding) {
            throw ValidationException::withMessages([
                'message' => trans('Retención no encontrada.')
            ]);
        }

        return new IncomeTaxWithholdingResource($incomeTaxWithholding);
    }
}
