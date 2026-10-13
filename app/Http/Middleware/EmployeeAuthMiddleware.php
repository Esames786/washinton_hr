<?php

namespace App\Http\Middleware;

use App\Models\EmployeeStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeeAuthMiddleware
{
    /**
     * Statuses that may sign in and use the portal normally.
     *
     * Resolved by NAME, never by id: Hello and CrazyRays are on separate databases since
     * the split, so the same status can hold a different auto-increment id on each side.
     *
     * Each of these exists because the agent has something to DO at that point — sign the
     * NDA, answer the questionnaire, upload documents, accept the contract. Blocking them
     * would make the step they were emailed about impossible.
     */
    private const ALLOWED = [
        EmployeeStatus::ACTIVE,
        EmployeeStatus::DOCS_VERIFICATION,
        EmployeeStatus::NDA_PENDING,
        EmployeeStatus::QUESTIONNAIRE_PENDING,
        EmployeeStatus::DOCUMENTS_PENDING,
        EmployeeStatus::CONTRACT_PENDING,
        EmployeeStatus::ONBOARDING,
    ];

    /**
     * Statuses where the agent may sign in but the portal is held.
     *
     * Client: after the questionnaire, "lock the screens" while interview, mock test and
     * offer happen offline; and at management approval "account will be locked until admin
     * change the status". They see a page explaining where they are, rather than being
     * told their account is inactive — which is what happened before.
     */
    private const LOCKED = [
        EmployeeStatus::IN_PROGRESS,
        EmployeeStatus::MANAGEMENT_APPROVAL,
    ];

    /** Reachable while locked, or the agent could not even sign out. */
    private const LOCK_EXEMPT_ROUTES = [
        'employee.locked',
        'employee.logout',
        'employee.activity.heartbeat',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('employee')->check()) {
            Log::warning('[EmployeeMiddleware] Not authenticated — redirecting to login', [
                'url'        => $request->fullUrl(),
                'session_id' => session()->getId(),
            ]);

            return redirect()->route('employee.login');
        }

        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        $user   = Auth::guard('employee')->user();
        $status = EmployeeStatus::nameFor($user->employee_status_id);

        // ── Held: signed in, but the portal is waiting on us, not on them ──
        if (in_array($status, self::LOCKED, true)) {
            if (in_array($request->route()?->getName(), self::LOCK_EXEMPT_ROUTES, true)) {
                return $next($request);
            }

            Log::info('[EmployeeMiddleware] Screens held', [
                'employee_id' => $user->id,
                'status'      => $status,
            ]);

            return redirect()->route('employee.locked');
        }

        // ── Not allowed in at all ──
        if (! in_array($status, self::ALLOWED, true)) {
            Log::warning('[EmployeeMiddleware] Blocked — status not allowed', [
                'employee_id' => $user->id,
                'status'      => $status,
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
