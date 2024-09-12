<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

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
        }

        foreach ($permissions as $permission) {
            if ($user->hasDirectPermission($permission)) {
                $hasPermission = true;
                break;
            }
        }
        // die();

        if ($hasPermission) {
            return $next($request);
        }

        abort(403, 'Sin autorización.');

        /*  if (!$user || !$user->hasDirectPermission($permission)) {
            abort(403, 'Sin autorización.');
        }

        return $next($request); */
    }
}
