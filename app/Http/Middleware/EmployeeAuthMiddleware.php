<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeeAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('employee')->check()) {
            Log::warning('[EmployeeMiddleware] Not authenticated — redirecting to login', [
                'url'        => $request->fullUrl(),
                'session_id' => session()->getId(),
                'has_session'=> $request->hasSession(),
            ]);
            return redirect()->route('employee.login');
        }

        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        $user = Auth::guard('employee')->user();

        Log::info('[EmployeeMiddleware] Authenticated user found', [
            'employee_id'        => $user->id,
            'email'              => $user->email,
            'employee_status_id' => $user->employee_status_id,
            'is_logged_in'       => $user->is_logged_in,
            'session_id'         => session()->getId(),
            'url'                => $request->fullUrl(),
        ]);

        // Status 7 = Document Verification — allow access so employee can upload documents
        $allowedStatuses = [1, 7];
        if (!in_array($user->employee_status_id, $allowedStatuses)) {
            Log::warning('[EmployeeMiddleware] Blocked — status not in allowed list', [
                'employee_id' => $user->id,
                'status'      => $user->employee_status_id,
            ]);
            Auth::guard('employee')->logout();
            return redirect()->route('employee.login')
                ->withErrors(['Your account is inactive. Please contact HR.']);
        }

        // Single login check
        if ($user->is_logged_in == 0) {
            Log::warning('[EmployeeMiddleware] Blocked — is_logged_in=0', [
                'employee_id' => $user->id,
                'email'       => $user->email,
            ]);
            Auth::guard('employee')->logout();
            return redirect()->route('employee.login')
                ->withErrors(['Your session has expired, please login again.']);
        }

        return $next($request);
    }
}
