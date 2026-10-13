<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Lets a shortlisted applicant choose their own password from the link in their email.
 *
 * No password is ever generated or emailed. The token is single-use and expires, so a
 * forwarded or leaked email does not leave a working credential lying around.
 */
class SetPasswordController extends Controller
{
    public const TOKEN_DAYS = 7;

    /** Issue a fresh token for an employee and return it (the caller builds the URL). */
    public static function issueToken(Employee $employee): string
    {
        $token = Str::random(64);

        $employee->set_password_token      = $token;
        $employee->set_password_expires_at = now()->addDays(self::TOKEN_DAYS);
        $employee->save();

        return $token;
    }

    public function show(string $token)
    {
        $employee = $this->resolve($token);

        if (! $employee) {
            return view('employee.auth.set_password_invalid');
        }

        return view('employee.auth.set_password', [
            'token'    => $token,
            'employee' => $employee,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min'       => 'Your password must be at least 8 characters.',
            'password.confirmed' => 'The two passwords do not match.',
        ]);

        $employee = $this->resolve($request->input('token'));

        if (! $employee) {
            return redirect()
                ->route('employee.login')
                ->with('error', 'That link has expired. Please contact us for a new one.');
        }

        $employee->password = Hash::make($request->input('password'));
        // Single use.
        $employee->set_password_token      = null;
        $employee->set_password_expires_at = null;
        $employee->save();

        Log::info('Employee set their own password', ['employee_id' => $employee->id]);

        return redirect()
            ->route('employee.login')
            ->with('success', 'Your password is set. You can sign in now.');
    }

    /** A token is only valid if it exists and has not expired. */
    private function resolve(?string $token): ?Employee
    {
        if (! $token) {
            return null;
        }

        return Employee::where('set_password_token', $token)
            ->where('set_password_expires_at', '>', now())
            ->first();
    }
}
