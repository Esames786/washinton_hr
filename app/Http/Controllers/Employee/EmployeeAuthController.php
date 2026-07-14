<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeAuthController extends Controller
{
    public function login(Request $request)
    {
        return view('employee.auth.login');
    }
    public function employee_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            // AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 0, 'errors' => $validator->errors()]);
            }
            return back()->withErrors($validator)->withInput();
        }

        $credentials = [
            'email'    => $request->input('email'),
            'password' => $request->input('password'),
        ];

        $employee = Employee::where('email', $request->input('email'))->first();

        \Illuminate\Support\Facades\Log::info('[EmployeeLogin] Attempt', [
            'email'        => $request->input('email'),
            'found'        => $employee ? 'yes' : 'no',
            'status'       => $employee?->employee_status_id,
            'is_logged_in' => $employee?->is_logged_in,
        ]);

        if (!$employee) {
            $error = ['password' => ['Subcontractor Not Found']];
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 0, 'errors' => $error]);
            }
            return back()->withErrors($error)->withInput();
        }

        $statusMessages = [
            2  => 'Your account has been marked inactive. Please contact HR.',
            3  => 'Your account has been terminated. Please contact HR.',
            4  => 'Your resignation has been processed. Access is no longer available.',
            5  => 'Your account is in training status. Please contact HR to activate.',
            6  => 'Your account is on trial. Please contact HR to activate.',
            8  => 'Your contract is pending. Please contact HR to complete the process.',
            9  => 'Your account is awaiting management approval. Please check back later.',
            10 => 'Your account is in deployed status. Please contact HR.',
        ];

        // Status 7 (Document Verification) — allow login so employee can upload documents
        if ($employee->employee_status_id != 1 && $employee->employee_status_id != 7) {
            $message = $statusMessages[$employee->employee_status_id]
                ?? 'Your account is not active. Please contact HR.';

            \Illuminate\Support\Facades\Log::warning('[EmployeeLogin] Blocked by status', [
                'email'  => $request->input('email'),
                'status' => $employee->employee_status_id,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status'     => 0,
                    'error_type' => 'account_status',
                    'errors'     => ['account' => [$message]],
                ]);
            }
            return back()->withErrors(['account' => $message])->withInput();
        }

        // Single-session restriction removed — employees may log in from multiple devices/browsers.

        $loggedIn = Auth::guard('employee')->attempt($credentials);

        // Fallback: the linked agent (washinton_agent `user`) account is the identity
        // source. HR records created during signup/campaign approval may carry a
        // different (random) password, so if the HR password doesn't match, verify
        // against the agent's password and sync it so future logins work directly.
        if (!$loggedIn && $employee->agent_id) {
            $agentHash = \Illuminate\Support\Facades\DB::table('user')
                ->where('id', $employee->agent_id)
                ->value('password');

            if ($agentHash && \Illuminate\Support\Facades\Hash::check($request->input('password'), $agentHash)) {
                $employee->password = $agentHash; // copy the bcrypt hash to keep them in sync
                $employee->save();
                Auth::guard('employee')->login($employee);
                $loggedIn = true;
            }
        }

        if ($loggedIn) {
            $now = Carbon::now();
            $employee->is_logged_in = 1;
            $employee->last_seen_at = $now;
            $employee->login_at     = $now->toDateString();
            $employee->save();

            // Explicitly save session — ensures auth data persists before redirect
            $request->session()->save();

            \Illuminate\Support\Facades\Log::info('[EmployeeLogin] Success', [
                'email'       => $request->input('email'),
                'employee_id' => $employee->id,
                'session_id'  => session()->getId(),
            ]);

            return redirect()->route('employee.dashboard');
        }

        $error = ['password' => ['Invalid email or password']];
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => 0, 'errors' => $error]);
        }
        return back()->withErrors($error)->withInput();
    }

    public function logout(Request $request)
    {
        $employee = Auth::guard('employee')->user();

        if ($employee) {
            $employee->is_logged_in = 0;
            $employee->save();
        }

        Auth::guard('employee')->logout();
        // Invalidate the session
        $request->session()->invalidate();
        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('employee.login'); // Default route
    }

}
