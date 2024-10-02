<?php

namespace App\Http\Controllers\Treasury\Taxes;

use App\Events\Treasury\Taxes\SocialSecurityTaxWithholdingEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Taxes\SocialSecurityTaxWithholdingRequest;
use App\Http\Resources\Treasury\Taxes\CategoryResource;
use App\Http\Resources\Treasury\Taxes\SocialSecurityTaxWithholdingResource;
use App\Models\Treasury\Taxes\Category;
use App\Models\Treasury\Taxes\SocialSecurityTaxWithholding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class SocialSecurityTaxWithholdingController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view social security tax withholdings')->only('index');
        $this->middleware('check.permission:create social security tax withholdings')->only('store');
        $this->middleware('check.permission:edit social security tax withholdings')->only('update');
        $this->middleware('check.permission:view users')->only('info');
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $category = Category::whereNot('id', 1)->orderBy('name', 'asc')->get();
        $socialSecurityTaxWithholding = SocialSecurityTaxWithholding::with(['category', 'userCreated', 'userUpdated'])->get();

        return response()->json([
            'categories' => CategoryResource::collection($category),
            'socialSecurityTaxWithholdings' => SocialSecurityTaxWithholdingResource::collection($socialSecurityTaxWithholding),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SocialSecurityTaxWithholdingRequest $request) {
        $socialSecurityTaxWithholding = SocialSecurityTaxWithholding::create([
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
        $socialSecurityTaxWithholding->load('category', 'userCreated', 'userUpdated');
        event(new SocialSecurityTaxWithholdingEvent($socialSecurityTaxWithholding, $tempUUID, 'create'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Retención agregada exitosamente.',
                'socialSecurityTaxWithholding' => $socialSecurityTaxWithholding,
            ],
            'success' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SocialSecurityTaxWithholdingRequest $request, SocialSecurityTaxWithholding $socialSecurityTaxWithholding) {
        $socialSecurityTaxWithholding->update([
            'rate' => $request->rate,
            'minAmount' => $request->minAmount,
            'fixedAmount' => $request->fixedAmount,
            'startAt' => date('Y-m-d', strtotime($request->startAt)),
            'endAt' => date('Y-m-d', strtotime($request->endAt)),
            'idUserUpdated' => Auth::id(),
            'updated_at' => now(),
        ]);

        $socialSecurityTaxWithholding->load('category', 'userCreated', 'userUpdated');
        event(new SocialSecurityTaxWithholdingEvent($socialSecurityTaxWithholding, $socialSecurityTaxWithholding->id, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Retención modificada exitosamente.',
            ],
            'success' => true,
        ]);
    }

    public function info(SocialSecurityTaxWithholding $socialSecurityTaxWithholding) {
        $socialSecurityTaxWithholding = SocialSecurityTaxWithholding::with(['userCreated', 'userUpdated'])->where('id', $socialSecurityTaxWithholding->id)->first();

        if (!$socialSecurityTaxWithholding) {
            throw ValidationException::withMessages([
                'message' => trans('Retención no encontrada.')
            ]);
        }

        return new SocialSecurityTaxWithholdingResource($socialSecurityTaxWithholding);
    }
}
