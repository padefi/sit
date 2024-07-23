<?php

namespace App\Http\Controllers\Treasury\Taxes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Treasury\Taxes\SocialSecurityTaxWithholdingRequest;
use App\Models\Treasury\Taxes\SocialSecurityTaxWithholding;
use Illuminate\Http\Request;

class SocialSecurityTaxWithholdingController extends Controller {
    public function __construct() {
        $this->middleware('check.permission:view social security withholdings')->only('index');
        $this->middleware('check.permission:create social security withholdings')->only('store');
        $this->middleware('check.permission:edit social security withholdings')->only('update');
        $this->middleware('check.permission:view users')->only('info');
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SocialSecurityTaxWithholdingRequest $request) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SocialSecurityTaxWithholdingRequest $request, SocialSecurityTaxWithholding $socialSecurityTaxWithholding) {
        //
    }
}
