<?php

namespace App\Http\Controllers\Bridge;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class HrBridgeController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        abort_unless(
            hash_equals((string) config('bridge.shared_key'), (string) $request->header('X-Bridge-Key')),
            401
        );

        $validator = Validator::make($request->all(), [
            'email'         => ['required', 'email'],
            'password'      => ['required', 'string'],
            'bridge_origin' => ['nullable', 'url', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $employee = Employee::where('email', $request->string('email')->toString())->first();

        if (!$employee || !Hash::check($request->string('password')->toString(), $employee->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 422);
        }

        if ((int) $employee->employee_status_id !== 1) {
            return response()->json(['message' => 'Employee is inactive.'], 403);
        }

        if ((int) $employee->is_logged_in === 1) {
            return response()->json(['message' => 'You are already logged in from another device/browser.'], 403);
        }

        $payload = Crypt::encryptString(json_encode([
            'employee_id'   => $employee->id,
            'issued_at'     => now()->timestamp,
            'bridge_origin' => $request->input('bridge_origin'),
        ]));

        return response()->json([
            'redirect_url' => URL::temporarySignedRoute(
                'bridge.hr.consume',
                now()->addMinutes(2),
                ['payload' => $payload]
            ),
        ]);
    }

    public function consume(Request $request): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        $payload = json_decode(
            Crypt::decryptString($request->query('payload')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $employee = Employee::findOrFail($payload['employee_id']);

        // DO NOT regenerate session — Chrome ignores the new cookie
        // Auth::login() writes auth data into the existing session
        Auth::guard('employee')->login($employee);

        // Explicitly save the session to ensure auth data is persisted
        $request->session()->save();

        $employee->is_logged_in = 1;
        $employee->last_seen_at = now();
        $employee->login_at    = now()->toDateString();
        $employee->save();

        \Illuminate\Support\Facades\Log::info('[BridgeConsume] Employee logged in via SSO', [
            'employee_id' => $employee->id,
            'session_id'  => $request->session()->getId(),
            'auth_check'  => Auth::guard('employee')->check(),
        ]);

        if (!empty($payload['bridge_origin'])) {
            return redirect(rtrim($payload['bridge_origin'], '/') . '/?verified=1');
        }

        return redirect()->route('employee.dashboard');
    }

    /**
     * DB-token based SSO consume — no signed URL, no session regeneration.
     * Token is stored in hr_sso_tokens, single-use, expires in 3 minutes.
     */
    public function consumeToken(Request $request, string $token): RedirectResponse
    {
        $record = \Illuminate\Support\Facades\DB::table('hr_sso_tokens')
            ->where('token', $token)
            ->first();

        if (!$record) {
            \Illuminate\Support\Facades\Log::warning('[SSO] Token not found', ['token' => substr($token, 0, 8)]);
            return redirect()->route('employee.login')->withErrors(['SSO link is invalid or already used.']);
        }

        if (now()->isAfter($record->expires_at)) {
            \Illuminate\Support\Facades\DB::table('hr_sso_tokens')->where('token', $token)->delete();
            \Illuminate\Support\Facades\Log::warning('[SSO] Token expired', ['employee_id' => $record->employee_id]);
            return redirect()->route('employee.login')->withErrors(['SSO link has expired. Please try again.']);
        }

        // Delete token immediately — single use
        \Illuminate\Support\Facades\DB::table('hr_sso_tokens')->where('token', $token)->delete();

        $employee = Employee::find($record->employee_id);

        if (!$employee) {
            return redirect()->route('employee.login')->withErrors(['Employee not found.']);
        }

        // Log in — writes to existing session, no regeneration
        Auth::guard('employee')->login($employee);

        $employee->is_logged_in = 1;
        $employee->last_seen_at = now();
        $employee->login_at     = now()->toDateString();
        $employee->save();

        // Force session write before redirect
        $request->session()->save();

        \Illuminate\Support\Facades\Log::info('[SSO] Token login success', [
            'employee_id' => $employee->id,
            'session_id'  => $request->session()->getId(),
        ]);

        // Redirect to requested destination
        $to = $request->query('to', 'dashboard');
        if ($to === 'profile') {
            return redirect()->route('employee.profile');
        }

        return redirect()->route('employee.dashboard');
    }

    public function createEmployee(Request $request): JsonResponse
    {
        abort_unless(
            hash_equals((string) config('bridge.shared_key'), (string) $request->header('X-Bridge-Key')),
            401
        );

        $validator = Validator::make($request->all(), [
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:75'],
            'password'       => ['required', 'string', 'min:8'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'address'        => ['nullable', 'string', 'max:255'],
            'country'        => ['nullable', 'string', 'max:100'],
            'state_id'       => ['nullable', 'string', 'max:100'],
            'user_type'      => ['nullable', 'string', 'max:50'],
            'agent_id'       => ['nullable', 'integer'],
            'shift_type_id'  => ['nullable', 'integer'],   // from signup form
            'account_type_id'=> ['nullable', 'integer'],   // 1=Salary, 2=Commission, 3=Salary+Commission
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $existing = Employee::where('email', $request->string('email')->toString())->first();

        if ($existing) {
            if ($existing->agent_id === null && $request->filled('agent_id')) {
                $existing->agent_id = (int) $request->input('agent_id');
                $existing->save();
            }
            return response()->json([
                'message'     => 'Employee already exists.',
                'employee_id' => $existing->id,
                'status'      => 'exists',
            ], 200);
        }

        // ── Resolve role, department, designation based on user_type ──────────
        // agent/broker/shipper → Order Taker (role_id=2, dept=1, desig=1)
        // carrier/dispatcher   → Dispatcher  (role_id=3, dept=2, desig=5)
        $userType = strtolower((string) $request->input('user_type', 'agent'));
        $isDispatcher = in_array($userType, ['carrier', 'dispatcher', 'broker_dispatcher'], true);

        $roleId        = $isDispatcher ? 3 : 2;   // 2=Order Taker, 3=Dispatcher (from PakistanReadySeeder)
        $departmentId  = $isDispatcher ? 2 : 1;   // 1=OT dept, 2=Dispatch dept
        $designationId = $isDispatcher ? 5 : 1;   // 1=Order Taker, 5=Dispatcher

        // Shift: use what user selected, default to Morning (1)
        $shiftId = (int) $request->input('shift_type_id', 1);

        // Account type: use what user selected, default to Salary+Commission (3)
        $accountTypeId = (int) $request->input('account_type_id', 3);

        // Commission: default to Standard 5% (id=1) for commission-based accounts
        $commissionId = in_array($accountTypeId, [2, 3]) ? 1 : null;

        // Gratuity: default to Standard (id=1) for salary accounts, No Gratuity (id=3) for commission-only
        $gratuityId = ($accountTypeId === 2) ? 3 : 1;

        // Tax slab: 0% exempt (id=1) for all new signups
        $taxSlabId = 1;
        $isTaxable = 0;

        // Basic salary: 0 for commission-only, 1 placeholder for others (admin sets real value)
        $basicSalary = ($accountTypeId === 2) ? 1 : 0;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $employee = new Employee();
            $employee->full_name          = $request->string('name')->toString();
            $employee->email              = $request->string('email')->toString();
            $employee->password           = Hash::make($request->string('password')->toString());
            $employee->employee_code      = $this->generateEmployeeCode();
            $employee->joining_date       = now()->toDateString();
            $employee->employment_type_id = 3;  // Probation for new signups
            $employee->employee_status_id = 7;  // Document Verification — pending docs
            $employee->phone              = $request->input('phone');
            $employee->address            = $request->input('address');
            $employee->country            = $request->input('country');
            $employee->cnic               = $request->input('state_id') ?? $request->input('cnic');
            $employee->agent_id           = $request->input('agent_id');
            $employee->role_id            = $roleId;
            $employee->department_id      = $departmentId;
            $employee->designation_id     = $designationId;
            $employee->shift_id           = $shiftId;
            $employee->account_type_id    = $accountTypeId;
            $employee->commission_id      = $commissionId;
            $employee->gratuity_id        = $gratuityId;
            $employee->valid_gratuity_date= now()->addYear()->toDateString();
            $employee->basic_salary       = $basicSalary;
            $employee->is_taxable         = $isTaxable;
            $employee->tax_slab_setting_id= $taxSlabId;
            // Extra personal fields from signup form
            $employee->father_name        = $request->input('father_name');
            $employee->dob                = $request->input('dob');
            $employee->gender             = $request->input('gender');
            $employee->marital_status     = $request->input('marital_status');
            $employee->city               = $request->input('city');
            $employee->state              = $request->input('state');
            $employee->created_by         = null;
            $employee->updated_by         = null;
            $employee->save();

            // ── Working days: Mon–Fri working, Sat–Sun off ────────────────────
            for ($day = 0; $day <= 6; $day++) {
                \Illuminate\Support\Facades\DB::table('hr_employee_working_days')->insert([
                    'employee_id' => $employee->id,
                    'day_of_week' => $day,
                    'is_working'  => in_array($day, [1,2,3,4,5]) ? 1 : 0,
                    'created_by'  => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // ── Assign default leaves ─────────────────────────────────────────
            $leaveYear = now()->year;
            foreach ([
                ['leave_type_id' => 1, 'assigned_quota' => 12],  // Casual
                ['leave_type_id' => 2, 'assigned_quota' => 10],  // Sick
                ['leave_type_id' => 3, 'assigned_quota' => 14],  // Annual
            ] as $leave) {
                \Illuminate\Support\Facades\DB::table('hr_employee_assign_leaves')->insert(array_merge($leave, [
                    'employee_id' => $employee->id,
                    'valid_from'  => "{$leaveYear}-01-01",
                    'valid_to'    => "{$leaveYear}-12-31",
                    'status'      => 1,
                    'created_by'  => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]));
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message'     => 'Employee created successfully.',
                'employee_id' => $employee->id,
                'status'      => 'created',
            ], 201);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('HrBridgeController createEmployee failed: ' . $e->getMessage());
            return response()->json(['message' => 'Employee creation failed.', 'error' => $e->getMessage()], 500);
        }
    }

    public function agentStatus(Request $request): JsonResponse
    {
        abort_unless(
            hash_equals((string) config('bridge.shared_key'), (string) $request->header('X-Bridge-Key')),
            401
        );

        $validator = Validator::make($request->all(), [
            'agent_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $employee = Employee::where('agent_id', (int) $request->agent_id)->first();

        if (!$employee) {
            return response()->json([
                'linked'  => false,
                'active'  => false,
                'message' => 'Employee not linked.',
            ], 200);
        }

        $isActive = (int) $employee->employee_status_id === 1;

        return response()->json([
            'linked'              => true,
            'active'              => $isActive,
            'employee_id'         => $employee->id,
            'employee_name'       => $employee->full_name,
            'employee_status_id'  => (int) $employee->employee_status_id,
            'message'             => $isActive ? 'Employee linked.' : 'Employee not active.',
        ], 200);
    }

    public function agentLogin(Request $request): JsonResponse
    {
        abort_unless(
            hash_equals((string) config('bridge.shared_key'), (string) $request->header('X-Bridge-Key')),
            401
        );

        $validator = Validator::make($request->all(), [
            'agent_id' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $employee = Employee::where('agent_id', (int) $request->agent_id)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not linked.'], 404);
        }

        // Allow status 1 (Active) and status 7 (Document Verification — can access to upload docs)
        if (!in_array((int) $employee->employee_status_id, [1, 7])) {
            return response()->json(['message' => 'Employee account is not active.'], 403);
        }

        // Generate a one-time DB token — avoids all session/cookie/signed-URL issues
        $token = \Illuminate\Support\Str::random(48);

        // Clean up old tokens for this employee
        \Illuminate\Support\Facades\DB::table('hr_sso_tokens')
            ->where('employee_id', $employee->id)
            ->delete();

        \Illuminate\Support\Facades\DB::table('hr_sso_tokens')->insert([
            'token'       => $token,
            'employee_id' => $employee->id,
            'expires_at'  => now()->addMinutes(3),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $redirectTo  = $request->input('redirect_to', 'dashboard'); // 'dashboard' or 'profile'
        $redirectUrl = rtrim(config('app.url'), '/') . '/employee/sso/' . $token . '?to=' . $redirectTo;

        return response()->json([
            'message'      => 'Redirect ready.',
            'redirect_url' => $redirectUrl,
        ], 200);
    }

    private function generateEmployeeCode(): string
    {
        do {
            $code = 'HT-' . now()->format('ymd') . '-' . random_int(1000, 9999);
        } while (Employee::where('employee_code', $code)->exists());

        return $code;
    }
}
