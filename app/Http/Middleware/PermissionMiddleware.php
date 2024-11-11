<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PermissionMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$permissions) {
        $user = Auth::user();
        $hasPermission = false;

        if (!$user) {
            abort(403, 'Sin autorización.');
            return Inertia::render('403');
        }

        if (!$user->is_active) {
            Auth::logout();
            return redirect('/');;
        }

        if ($user->reset_password) {
            Auth::logout();
            throw ValidationException::withMessages([
                'message' => trans('Debe cambiar la contraseña.'),
                'changePassword' => true,
            ]);

            return redirect('/');;
        }

        foreach ($permissions as $permission) {
            if ($user->hasDirectPermission($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if ($hasPermission) {
            return $next($request);
        }

        return Inertia::render('403');
    }
}
