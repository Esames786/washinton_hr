<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PermissionMiddleware
{

    public function handle($request, Closure $next, $guard = null)
    {
        $routeName = $request->route()->getName();
//        if ($guard && !Auth::guard($guard)->check()) {
//            return redirect()->route('admin.login');
//        }

        $user = Auth::guard($guard)->user();
        $excludedRoutes=[
            'admin.logout',
            'admin.dashboard',
            'admin.not_found',
        ];

        if ($routeName && !$user->can($routeName) && $user->role_id!=1 && !in_array($routeName, $excludedRoutes)) {
            abort(403, 'Unauthorized');
//            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
