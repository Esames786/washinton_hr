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
    public function employee_login(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'errors' => $validator->errors()
            ]);
        }

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        $employee = Employee::where('email', $request->input('email'))->first();

        \Illuminate\Support\Facades\Log::info('[EmployeeLogin] Attempt', [
            'email'  => $request->input('email'),
            'found'  => $employee ? 'yes' : 'no',
            'status' => $employee?->employee_status_id,
            'is_logged_in' => $employee?->is_logged_in,
        ]);

        if (!$employee) {
            return response()->json([
                'status' => 0,
                'errors' => ['password' => ['Employee Not Found']],
            ]);
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
                'message' => $message,
            ]);

            return response()->json([
                'status'     => 0,
                'error_type' => 'account_status',
                'errors'     => ['account' => [$message]],
            ]);
        }

        if ($employee->is_logged_in == 1) {
            \Illuminate\Support\Facades\Log::warning('[EmployeeLogin] Already logged in', [
                'email' => $request->input('email'),
            ]);
            return response()->json([
                'status' => 0,
                'errors' => ['account' => ['You are already logged in from another device/browser']],
            ]);
        }

        if (Auth::guard('employee')->attempt($credentials)) {
            $now = Carbon::now();
            $employee->is_logged_in = 1;
            $employee->last_seen_at = $now;
            $employee->login_at     = $now->toDateString();
            $employee->save();

            // Regenerate session to ensure the new session ID is sent in the response cookie
            $request->session()->regenerate();

            \Illuminate\Support\Facades\Log::info('[EmployeeLogin] Success', [
                'email'           => $request->input('email'),
                'employee_id'     => $employee->id,
                'new_session_id'  => session()->getId(),
            ]);

            return response()->json([
                'status'       => 1,
                'message'      => 'Login successful',
                'redirect_url' => route('employee.dashboard'),
            ]);
        }

        \Illuminate\Support\Facades\Log::warning('[EmployeeLogin] Wrong password', [
            'email' => $request->input('email'),
        ]);

        return response()->json([
            'status' => 0,
            'errors' => ['password' => ['Invalid email or password']],
        ]);
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
