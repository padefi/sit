<?php

use App\Http\Controllers\Treasury\Taxes\IncomeTaxWithholdingController;
use App\Http\Controllers\Treasury\Taxes\IncomeTaxWithholdingScaleController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Treasury\Voucher\VoucherSubtypeController;
use App\Http\Controllers\Treasury\Voucher\VoucherExpenseController;
use App\Http\Controllers\Treasury\Bank\BankController;
use App\Http\Controllers\Treasury\Bank\BankAccountController;
use App\Http\Controllers\Treasury\Voucher\VoucherTypeController;
use App\Http\Controllers\Treasury\supplier\SupplierController;
use App\Http\Controllers\Treasury\Taxes\SocialSecurityTaxWithholdingController;
use App\Http\Controllers\Treasury\Taxes\VatTaxWithholdingController;
use App\Http\Controllers\Treasury\Voucher\VoucherController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Auth/Login');
})->middleware('guest');

Route::get('/home', function () {
    return Inertia::render('Home');
})->middleware(['auth', 'verified'])->name('home');

/* Route::middleware(['auth', 'role:admin|tesorero'])->group(function () {
    Route::resource('users', UserController::class);
    Route::put('/users/{user}/update-permission', [UserController::class, 'updatePermission'])->name('users.updatePermission');
    Route::resource('roles', RoleController::class);
    // Route::resource('permissions', PermissionController::class);
}); */

Route::group(['middleware' => ['auth', 'check.permission:view users']], function () {
    Route::resource('users', UserController::class);
    Route::put('/users/{user}/update-permission', [UserController::class, 'updatePermission'])->name('users.updatePermission');
});

Route::group(['middleware' => ['auth', 'check.permission:view banks']], function () {
    Route::resource('banks', BankController::class);
    Route::get('/banks/{bank}/info', [BankController::class, 'info'])->name('banks.info');
    Route::resource('bankAccounts', BankAccountController::class);
    Route::get('/bankAccounts/{bankAccount}/info', [BankAccountController::class, 'info'])->name('bankAccounts.info');
});

Route::group(['middleware' => ['auth', 'check.permission:view voucher types']], function () {
    Route::resource('voucher-types', VoucherTypeController::class);
    Route::post('/voucher-types/{voucher_type}/relate', [VoucherTypeController::class, 'relate'])->name('voucher-types.relate');
});

Route::group(['middleware' => ['auth', 'check.permission:view voucher subtypes']], function () {
    Route::resource('voucher-subtypes', VoucherSubtypeController::class);
    Route::get('/voucher-subtypes/{voucher_subtype}/info', [VoucherSubtypeController::class, 'info'])->name('voucher-subtypes.info');
    Route::post('/voucher-subtypes/{voucher_subtype}/relate', [VoucherSubtypeController::class, 'relate'])->name('voucher-subtypes.relate');
});

Route::group(['middleware' => ['auth', 'check.permission:view voucher expenses']], function () {
    Route::resource('voucher-expenses', VoucherExpenseController::class);
    Route::get('/voucher-expenses/{voucher_expense}/info', [VoucherExpenseController::class, 'info'])->name('voucher-expenses.info');
});

Route::group(['middleware' => ['auth', 'check.permission:view suppliers']], function () {
    Route::resource('suppliers', SupplierController::class);
    Route::get('/suppliers/{supplier}/info', [SupplierController::class, 'info'])->name('suppliers.info');
    Route::get('/voucher-subtypes/{voucher_type}/data-related', [VoucherSubtypeController::class, 'dataRelated'])->name('voucher-subtypes.data-related');
    Route::get('/voucher-expenses/{voucher_subtype}/data-related', [VoucherExpenseController::class, 'dataRelated'])->name('voucher-expenses.data-related');
});

Route::group(['middleware' => ['auth', 'check.permission:view vouchers']], function () {
    Route::resource('vouchers', VoucherController::class);
    Route::get('/vouchers/{voucher_type}/types-related', [VoucherController::class, 'typesRelated'])->name('vouchers.types-related');
    Route::get('/vouchers/{invoice_type}/invoice-types-related', [VoucherController::class, 'invoiceTypesRelated'])->name('vouchers.invoice-types-related');
    Route::get('/vouchers/{voucher}/info', [VoucherController::class, 'info'])->name('vouchers.info');
});

Route::group(['middleware' => ['auth', 'check.permission:view income tax withholdings', 'check.permission:view social security tax withholdings']], function () {
    Route::get('/taxes', function () {
        return Inertia::render('Treasury/Taxes/Taxes');
    })->name('taxes.index');

    Route::resource('incomeTaxWithholdings', IncomeTaxWithholdingController::class);
    Route::get('/incomeTaxWithholdings/{incomeTaxWithholding}/info', [IncomeTaxWithholdingController::class, 'info'])->name('incomeTaxWithholdings.info');

    Route::resource('incomeTaxWithholdingScales', IncomeTaxWithholdingScaleController::class);
    Route::get('/incomeTaxWithholdingScales/{incomeTaxWithholdingScale}/info', [IncomeTaxWithholdingScaleController::class, 'info'])->name('incomeTaxWithholdingScale.info');

    Route::resource('socialSecurityTaxWithholdings', SocialSecurityTaxWithholdingController::class);
    Route::get('/socialSecurityTaxWithholdings/{socialSecurityTaxWithholding}/info', [SocialSecurityTaxWithholdingController::class, 'info'])->name('socialSecurityTaxWithholdings.info');

    Route::resource('vatTaxWithholdings', VatTaxWithholdingController::class);
    Route::get('/vatTaxWithholdings/{vatTaxWithholding}/info', [VatTaxWithholdingController::class, 'info'])->name('vatTaxWithholdings.info');
});

Route::middleware('auth')->group(function () {
    /* Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); */
});

require __DIR__ . '/auth.php';
