<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Users\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use ParagonIE\Sodium\Core\Curve25519\H;

class AuthController extends Controller {
    /**
     * Display the login view.
     */
    public function create(): Response {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse {
        $request->authenticate();
        $request->session()->regenerate();
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function changePassword(ChangePasswordRequest $request) {
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'message' => trans('Usuario y/o contraseña incorrectos.')
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'message' => trans('Usuario bloqueado.')
            ]);
        }

        if (!Hash::check($request->currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'message' => trans('Usuario y/o contraseña incorrectos.')
            ]);
        }

        $user->update([
            'password' => Hash::make($request->newPassword)
        ]);

        Auth::logout();

        return Redirect::back()->with([
            'info' => [
                'type' => 'success',
                'message' => 'Contraseña modificada exitosamente.',
            ],
            'success' => true,
        ]);
    }
}
