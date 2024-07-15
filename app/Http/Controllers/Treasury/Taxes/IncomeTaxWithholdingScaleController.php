<?php

namespace App\Http\Controllers\Treasury\Taxes;

use App\Http\Controllers\Controller;
use App\Http\Resources\Treasury\Taxes\CategoryResource;
use App\Http\Resources\Treasury\Taxes\IncomeTaxWithholdingScaleResource;
use App\Models\Treasury\Taxes\Category;
use App\Models\Treasury\Taxes\IncomeTaxWithholdingScale;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomeTaxWithholdingScaleController extends Controller {
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
        $category = Category::orderBy('name', 'asc')->get();
        $incomeTaxWithholdingScale = IncomeTaxWithholdingScale::with(['userCreated', 'userUpdated'])->get();

        return Inertia::render('Treasury/Taxes/IncomeTaxWithholdingsScalesIndex', [
            'categories' => CategoryResource::collection($category),
            'incomeTaxWithholdingsScales' => IncomeTaxWithholdingScaleResource::collection($incomeTaxWithholdingScale),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
        //
    }
}
