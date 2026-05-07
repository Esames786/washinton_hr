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
        // Wrap in try-catch to catch any silent exceptions (e.g. Spatie permission table issues)
        try {
            $isAuth = Auth::guard('admin')->check();
        } catch (\Throwable $e) {
            Log::error('[AdminMiddleware] Exception during auth check', [
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
                'url'        => $request->fullUrl(),
                'session_id' => session()->getId(),
            ]);
            return redirect()->route('admin.login');
        }

        if (!$isAuth) {
            // Try to get the raw session data to understand what's stored
            $sessionAuthKey = 'login_admin_' . sha1(static::class);
            $allSessionKeys = array_keys(session()->all());
            $authKeys = array_filter($allSessionKeys, fn($k) => str_starts_with($k, 'login_'));

            Log::warning('[AdminMiddleware] Not authenticated', [
                'url'             => $request->fullUrl(),
                'session_id'      => session()->getId(),
                'session_keys'    => $allSessionKeys,
                'auth_keys_found' => array_values($authKeys),
                'cookie_name'     => config('session.cookie'),
                'cookies_sent'    => array_keys($request->cookies->all()),
            ]);
            return redirect()->route('admin.login');
        }

        try {
            $user = Auth::guard('admin')->user();
        } catch (\Throwable $e) {
            Log::error('[AdminMiddleware] Exception getting user', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login');
        }

        Log::info('[AdminMiddleware] Authenticated', [
            'admin_id'   => $user->id,
            'email'      => $user->email,
            'status'     => $user->status,
            'session_id' => session()->getId(),
        ]);

        if (Auth::guard('employee')->check()) {
            Auth::guard('employee')->logout();
        }

        if ($user->status != 1) {
            Log::warning('[AdminMiddleware] Blocked — status not active', [
                'admin_id' => $user->id,
            ]);
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')
                ->withErrors(['Your account is inactive']);
        }

        return $next($request);
    }
}
