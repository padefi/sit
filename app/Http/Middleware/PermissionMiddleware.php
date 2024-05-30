<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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

        if (!$user || !$user->hasPermissionTo($permission)) {
            abort(403, 'Sin autorización.');
        }

        return $next($request);
    }
}
