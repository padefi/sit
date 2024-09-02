<?php

namespace App\Http\Controllers\Treasury\Taxes;

use App\Events\Treasury\Taxes\IncomeTaxWithholdingScaleEvent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Treasury\Taxes\IncomeTaxWithholdingScaleRequest;
use App\Http\Resources\Treasury\Taxes\IncomeTaxWithholdingScaleResource;
use App\Models\Treasury\Taxes\IncomeTaxWithholding;
use App\Models\Treasury\Taxes\IncomeTaxWithholdingScale;
use App\Models\Treasury\Taxes\IncomeTaxWithholdingTable;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

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
        $incomeTaxWithholdingScale = IncomeTaxWithholdingScale::create([
            'idCat' => $request->idCat,
            'rate' => $request->rate,
            'minAmount' => $request->minAmount,
            'maxAmount' => $request->maxAmount,
            'fixedAmount' => $request->fixedAmount,
            'startAt' => date('Y-m-d', strtotime($request->startAt)),
            'endAt' => date('Y-m-d', strtotime($request->endAt)),
            'idUserCreated' => auth()->user()->id,
            'created_at' => now(),
            'updated_at' => null,
        ]);

        $incomeTaxWithholdingTable = IncomeTaxWithholdingTable::where('idCat', $request->idCat)->first();

        if ($incomeTaxWithholdingTable->table === 'normal') {
            $incomeTaxWithholding = IncomeTaxWithholding::where('idCat', $request->idCat)->first();

            IncomeTaxWithholdingScale::create([
                'idCat' => $request->idCat,
                'rate' => $incomeTaxWithholding->rate,
                'minAmount' => $incomeTaxWithholding->minAmount,
                'maxAmount' => 0,
                'fixedAmount' => $incomeTaxWithholding->fixedAmount,
                'startAt' => date('Y-m-d', strtotime($incomeTaxWithholding->startAt)),
                'endAt' => date('Y-m-d', strtotime($incomeTaxWithholding->endAt)),
                'idUserCreated' => auth()->user()->id,
                'created_at' => now(),
                'updated_at' => null,
            ]);

            IncomeTaxWithholding::destroy($incomeTaxWithholding->id);
            DB::table('income_tax_withholding_tables')
                ->where('idCat', $request->idCat)
                ->update(['table' => 'scale']);
        }
        
        $tempUUID = $request->keys()[7];
        $incomeTaxWithholdingScale->load('category', 'userCreated', 'userUpdated');
        event(new IncomeTaxWithholdingScaleEvent($incomeTaxWithholdingScale, $tempUUID, 'create'));

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Retención agregada exitosamente.',
                'incomeTaxWithholding' => $incomeTaxWithholdingScale,
            ],
            'success' => true,
        ]);
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

    public function info(IncomeTaxWithholdingScale $incomeTaxWithholdingScale) {
        $incomeTaxWithholdingScale = IncomeTaxWithholdingScale::with(['userCreated', 'userUpdated'])->where('id', $incomeTaxWithholdingScale->id)->first();

        if (!$incomeTaxWithholdingScale) {
            throw ValidationException::withMessages([
                'message' => trans('Retención no encontrada.')
            ]);
        }

        return new IncomeTaxWithholdingScaleResource($incomeTaxWithholdingScale);
    }
}
