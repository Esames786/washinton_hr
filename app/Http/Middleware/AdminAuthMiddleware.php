<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('admin')->check()) {
            Log::warning('[AdminMiddleware] Not authenticated — redirecting to login', [
                'url'        => $request->fullUrl(),
                'session_id' => session()->getId(),
                'has_session'=> $request->hasSession(),
            ]);
            return redirect()->route('admin.login');
        }

        if (Auth::guard('employee')->check()) {
            Auth::guard('employee')->logout();
        }

        $user = Auth::guard('admin')->user();

        Log::info('[AdminMiddleware] Authenticated admin found', [
            'admin_id'   => $user->id,
            'email'      => $user->email,
            'status'     => $user->status,
            'session_id' => session()->getId(),
            'url'        => $request->fullUrl(),
        ]);

        if ($user->status != 1) {
            Log::warning('[AdminMiddleware] Blocked — admin status not active', [
                'admin_id' => $user->id,
                'status'   => $user->status,
            ]);
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')
                ->withErrors(['Your account is inactive']);
        }

        return $next($request);
    }
}
