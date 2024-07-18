<?php

namespace App\Http\Controllers\Treasury\Taxes;

use App\Events\Treasury\Taxes\IncomeTaxWithholdingScaleEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Taxes\IncomeTaxWithholdingScaleRequest;
use App\Http\Resources\Treasury\Taxes\IncomeTaxWithholdingScaleResource;
use App\Models\Treasury\Taxes\IncomeTaxWithholdingScale;
use Illuminate\Support\Facades\Redirect;

class IncomeTaxWithholdingScaleController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view income tax withholdings')->only('index');
        $this->middleware('check.permission:create income tax withholdings')->only('store');
        $this->middleware('check.permission:edit income tax withholdings')->only('update');
        $this->middleware('check.permission:view users')->only('info');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncomeTaxWithholdingScaleRequest $request) {
        var_dump($request->all());
        die();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IncomeTaxWithholdingScaleRequest $request, IncomeTaxWithholdingScale $incomeTaxWithholdingScale) {
        $incomeTaxWithholdingScale->update([
            'rate' => $request->rate,
            'minAmount' => $request->minAmount,
            'maxAmount' => $request->maxAmount,
            'fixedAmount' => $request->fixedAmount,
            'startAt' => date('Y-m-d', strtotime($request->startAt)),
            'endAt' => date('Y-m-d', strtotime($request->endAt)),
            'idUserUpdated' => auth()->user()->id,
            'updated_at' => now(),
        ]);

        $incomeTaxWithholdingScale->load('category', 'userCreated', 'userUpdated');
        event(new IncomeTaxWithholdingScaleEvent($incomeTaxWithholdingScale, $incomeTaxWithholdingScale->id, 'update'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Retención modificada exitosamente.',
            ],
            'success' => true,
        ]);
    }
}
