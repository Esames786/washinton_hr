<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {


        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        if (Auth::guard('employee')->check()) {
            Auth::guard('employee')->logout();
        }

        $user = Auth::guard('admin')->user();

        // Status check
        if ($user->status != 1) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')
                ->withErrors(['Your account is inactive']);
        }

        // Role check (Spatie)
//        if (!$user->hasRole('Admin')) {
//            abort(403, 'Unauthorized');
//        }

        return $next($request);
    }
}
