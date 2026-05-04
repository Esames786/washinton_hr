<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeAuthMiddleware
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

        if (!Auth::guard('employee')->check()) {
            return redirect()->route('employee.login');
        }

        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        $user = Auth::guard('employee')->user();

        // Check Employee Active or not
        if ($user->employee_status_id != 1) {
            Auth::guard('employee')->logout();
            return redirect()->route('employee.login')
                ->withErrors(['Your account is inactive']);
        }

        //Single login check
        if ($user->is_logged_in == 0) {
            Auth::guard('employee')->logout();
            return redirect()->route('employee.login')
                ->withErrors(['Your session has expired, please login again.']);
        }



//
//        if (!$user->last_seen_at || $user->last_seen_at->lt(now()->subMinute())) {
//            $user->last_seen_at = now();
//            $user->save();
//        }

//        // Role check (Spatie)
//        if (!$user->hasRole('employee')) {
//            abort(403, 'Unauthorized');
//        }

        return $next($request);
    }
}
