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
use App\Http\Controllers\Treasury\TreasuryVoucher\PaymentMethodController;
use App\Http\Controllers\Treasury\TreasuryVoucher\TreasuryVoucherController;
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
    Route::put('/users/{user}/update-permission', [UserController::class, 'updatePermission'])->name('users.updatePermission');
    Route::resource('users', UserController::class);
});

Route::group(['middleware' => ['auth', 'check.permission:view banks']], function () {
    Route::get('/banks/{bank}/info', [BankController::class, 'info'])->name('banks.info');
    Route::get('/banks/show-banks', [BankController::class, 'showBanks'])->name('banks.show-banks');
    Route::resource('banks', BankController::class);
    Route::get('/bankAccounts/{bankAccount}/info', [BankAccountController::class, 'info'])->name('bankAccounts.info');
    Route::resource('bankAccounts', BankAccountController::class);
});

Route::group(['middleware' => ['auth', 'check.permission:view voucher types']], function () {
    Route::get('/voucher-types/data', [VoucherTypeController::class, 'data'])->name('voucher-types.data');
    Route::post('/voucher-types/{voucher_type}/relate', [VoucherTypeController::class, 'relate'])->name('voucher-types.relate');
    Route::resource('voucher-types', VoucherTypeController::class);
});

Route::group(['middleware' => ['auth', 'check.permission:view voucher subtypes']], function () {
    Route::get('/voucher-subtypes/{voucher_subtype}/info', [VoucherSubtypeController::class, 'info'])->name('voucher-subtypes.info');
    Route::post('/voucher-subtypes/{voucher_subtype}/relate', [VoucherSubtypeController::class, 'relate'])->name('voucher-subtypes.relate');
    Route::resource('voucher-subtypes', VoucherSubtypeController::class);
});

Route::group(['middleware' => ['auth', 'check.permission:view voucher expenses']], function () {
    Route::get('/voucher-expenses/{voucher_expense}/info', [VoucherExpenseController::class, 'info'])->name('voucher-expenses.info');
    Route::resource('voucher-expenses', VoucherExpenseController::class);
});

Route::group(['middleware' => ['auth', 'check.permission:view suppliers']], function () {
    Route::get('/suppliers/{supplier}/info', [SupplierController::class, 'info'])->name('suppliers.info');
    Route::get('/suppliers/data', [SupplierController::class, 'data'])->name('suppliers.data');
    Route::get('/voucher-subtypes/{voucher_type}/data-related', [VoucherSubtypeController::class, 'dataRelated'])->name('voucher-subtypes.data-related');
    Route::get('/voucher-expenses/{voucher_subtype}/data-related', [VoucherExpenseController::class, 'dataRelated'])->name('voucher-expenses.data-related');
    Route::resource('suppliers', SupplierController::class);
});

Route::group(['middleware' => ['auth', 'check.permission:view vouchers']], function () {
    Route::get('/invoice-types', [VoucherController::class, 'invoiceTypes'])->name('vouchers.invoice-types');
    Route::get('/show-vouchers/{supplier}', [VoucherController::class, 'showVouchers'])->name('vouchers.show-vouchers');
    Route::get('/vouchers/{voucher_type}/types-related', [VoucherController::class, 'typesRelated'])->name('vouchers.types-related');
    Route::get('/vouchers/{invoice_type}/invoice-types-related', [VoucherController::class, 'invoiceTypesRelated'])->name('vouchers.invoice-types-related');
    Route::get('/vouchers/{voucher}/info', [VoucherController::class, 'info'])->name('vouchers.info');
    Route::put('/vouchers/{voucher}/void', [VoucherController::class, 'voidVoucher'])->name('vouchers.void');
    Route::get('/vouchers/{voucher}/pending-to-pay', [VoucherController::class, 'vouchersPendingToPay'])->name('vouchers.pending-to-pay');
    Route::post('/vouchers/voucher-to-treasury', [VoucherController::class, 'voucherToTreasury'])->name('vouchers.voucher-to-treasury');
    Route::resource('vouchers', VoucherController::class);
    Route::resource('payment-methods', PaymentMethodController::class);
});

Route::group(['middleware' => ['auth', 'check.permission:view treasury vouchers']], function () {
    Route::get('/treasury-vouchers/status', [TreasuryVoucherController::class, 'treasuryVoucherStatus'])->name('treasury-vouchers.status');
    Route::get('/treasury-vouchers/{voucher_type}/{voucher_status}', [TreasuryVoucherController::class, 'treasuryVouchers'])->name('treasury-vouchers.get-treasury-vouchers');
    Route::post('/treasury-voucher/{treasury_voucher}/calculate-withholding-tax', [TreasuryVoucherController::class, 'calculateWithholdingTax'])->name('treasury-vouchers.calculate-withholding-tax');
    Route::get('/treasury-voucher/{treasury_voucher}/info', [TreasuryVoucherController::class, 'info'])->name('treasury-voucher.info');
    Route::put('/treasury-vouchers/confirm', [TreasuryVoucherController::class, 'confirmTreasuryVoucher'])->name('treasury-vouchers.confirm');
    Route::put('/treasury-vouchers/{treasury_voucher}/void', [TreasuryVoucherController::class, 'voidTreasuryVoucher'])->name('treasury-vouchers.void');
    Route::resource('treasury-vouchers', TreasuryVoucherController::class);
});


Route::group(['middleware' => ['auth', 'check.permission:view income tax withholdings', 'check.permission:view social security tax withholdings']], function () {
    Route::get('/taxes', function () {
        return Inertia::render('Treasury/Taxes/Taxes');
    })->name('taxes.index');

    Route::get('/incomeTaxWithholdings/{incomeTaxWithholding}/info', [IncomeTaxWithholdingController::class, 'info'])->name('incomeTaxWithholdings.info');
    Route::resource('incomeTaxWithholdings', IncomeTaxWithholdingController::class);

    Route::get('/incomeTaxWithholdingScales/{incomeTaxWithholdingScale}/info', [IncomeTaxWithholdingScaleController::class, 'info'])->name('incomeTaxWithholdingScale.info');
    Route::resource('incomeTaxWithholdingScales', IncomeTaxWithholdingScaleController::class);

    Route::get('/socialSecurityTaxWithholdings/{socialSecurityTaxWithholding}/info', [SocialSecurityTaxWithholdingController::class, 'info'])->name('socialSecurityTaxWithholdings.info');
    Route::resource('socialSecurityTaxWithholdings', SocialSecurityTaxWithholdingController::class);

    Route::get('/vatTaxWithholdings/{vatTaxWithholding}/info', [VatTaxWithholdingController::class, 'info'])->name('vatTaxWithholdings.info');
    Route::resource('vatTaxWithholdings', VatTaxWithholdingController::class);
});

Route::middleware('auth')->group(function () {
    /* Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); */
});

require __DIR__ . '/auth.php';
