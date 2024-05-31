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
    public function handle($request, Closure $next, $permission) {
        $user = Auth::user();

        if (!$user || !$user->hasDirectPermission($permission)) {
            abort(403, 'Sin autorización.');
        }

        return $next($request);
    }
}
