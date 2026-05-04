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

        Auth::guard('employee')->login($employee);
        $request->session()->regenerate();

        $employee->is_logged_in = 1;
        $employee->last_seen_at = now();
        $employee->login_at    = now()->toDateString();
        $employee->save();

        if (!empty($payload['bridge_origin'])) {
            return redirect(rtrim($payload['bridge_origin'], '/') . '/?verified=1');
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
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:75'],
            'password'  => ['required', 'string', 'min:8'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'address'   => ['nullable', 'string', 'max:255'],
            'country'   => ['nullable', 'string', 'max:100'],
            'state_id'  => ['nullable', 'string', 'max:100'],
            'user_type' => ['nullable', 'string', 'max:50'],
            'agent_id'  => ['nullable', 'integer'],
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

        $employee = new Employee();
        $employee->full_name          = $request->string('name')->toString();
        $employee->email              = $request->string('email')->toString();
        $employee->password           = Hash::make($request->string('password')->toString());
        $employee->employee_code      = $this->generateEmployeeCode();
        $employee->joining_date       = now()->toDateString();
        $employee->employment_type_id = (int) config('bridge.defaults.employment_type_id', 3);
        $employee->employee_status_id = (int) config('bridge.defaults.employee_status_id', 7);
        $employee->phone              = $request->input('phone');
        $employee->address            = $request->input('address');
        $employee->country            = $request->input('country');
        $employee->cnic               = $request->input('state_id');
        $employee->agent_id           = $request->input('agent_id');
        $employee->created_by         = null;
        $employee->updated_by         = null;
        $employee->save();

        return response()->json([
            'message'     => 'Employee created successfully.',
            'employee_id' => $employee->id,
            'status'      => 'created',
        ], 201);
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

        if ((int) $employee->employee_status_id !== 1) {
            return response()->json(['message' => 'Employee not active.'], 403);
        }

        $payload = Crypt::encryptString(json_encode([
            'employee_id' => $employee->id,
            'issued_at'   => now()->timestamp,
        ]));

        return response()->json([
            'message'      => 'Redirect ready.',
            'redirect_url' => URL::temporarySignedRoute(
                'bridge.hr.consume',
                now()->addMinutes(2),
                ['payload' => $payload]
            ),
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
