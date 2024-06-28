<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Treasury\Voucher\VoucherSubtypeController;
use App\Http\Controllers\Treasury\Voucher\VoucherExpenseController;
use App\Http\Controllers\Treasury\Bank\BankController;
use App\Http\Controllers\Treasury\Bank\BankAccountController;

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

Route::group(['middleware' => ['auth', 'check.permission:view voucher subtypes']], function () {
    Route::resource('voucher-subtypes', VoucherSubtypeController::class);
    Route::get('/voucher-subtypes/{voucher_subtype}/info', [VoucherSubtypeController::class, 'info'])->name('voucher-subtypes.info');
});

Route::group(['middleware' => ['auth', 'check.permission:view voucher expenses']], function () {
    Route::resource('voucher-expenses', VoucherExpenseController::class);
    Route::get('/voucher-expenses/{voucher_expense}/info', [VoucherExpenseController::class, 'info'])->name('voucher-expenses.info');
});

Route::group(['middleware' => ['auth', 'check.permission:view banks']], function () {
    Route::resource('banks', BankController::class);
    Route::get('/banks/{bank}/info', [BankController::class, 'info'])->name('banks.info');
    Route::resource('bankAccounts', BankAccountController::class);
    Route::get('/bankAccounts/{bankAccount}/info', [BankAccountController::class, 'info'])->name('bankAccounts.info');
});

Route::middleware('auth')->group(function () {
    /* Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); */
});

require __DIR__ . '/auth.php';
