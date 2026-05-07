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


//        if (Auth::guard('admin')->check()) {
//            Auth::guard('admin')->logout();
//        }


        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        $employee = Employee::where('email',$request->input('email'))->first();

        if(!$employee){
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
        // All other non-active statuses are blocked
        if ($employee->employee_status_id != 1 && $employee->employee_status_id != 7) {
            $message = $statusMessages[$employee->employee_status_id]
                ?? 'Your account is not active. Please contact HR.';

            return response()->json([
                'status'     => 0,
                'error_type' => 'account_status',
                'errors'     => ['account' => [$message]],
            ]);
        }

        if ($employee->is_logged_in == 1) {
            return response()->json([
                'status' => 0,
                'errors' => ['account' => ['You are already logged in from another device/browser']],
            ]);
        }

        if (Auth::guard('employee')->attempt($credentials)) {

            $now = Carbon::now();
            $employee->is_logged_in = 1;
            $employee->last_seen_at = $now;
            $employee->login_at =$now->toDateString();
            $employee->save();

            // Authentication passed
            return response()->json([
                'status' => 1,
                'message' => 'Login successful',
            ]);
        }

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
