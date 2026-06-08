<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\Role;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     *
     * Sync newly created employee to washinton_agent via bridge endpoint.
     *
     * @param Employee $employee
     * @return void
     */
    public function created(Employee $employee)
    {
        try {
            $this->syncEmployeeToAgent($employee);
        } catch (\Throwable $e) {
            // Log error but don't break employee creation in HR
            Log::error('EmployeeObserver: Failed to sync employee to washinton_agent', [
                'employee_id' => $employee->id,
                'email'       => $employee->email,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the Employee "updated" event.
     *
     * Sync updated employee data to washinton_agent.
     *
     * @param Employee $employee
     * @return void
     */
    public function updated(Employee $employee)
    {
        // Only sync if key fields changed
        if ($employee->isDirty(['full_name', 'email', 'phone', 'role_id'])) {
            try {
                $this->syncEmployeeToAgent($employee);
            } catch (\Throwable $e) {
                Log::error('EmployeeObserver: Failed to sync updated employee to washinton_agent', [
                    'employee_id' => $employee->id,
                    'email'       => $employee->email,
                    'error'       => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Sync employee to washinton_agent via bridge endpoint
     *
     * @param Employee $employee
     * @return void
     */
    private function syncEmployeeToAgent(Employee $employee): void
    {
        $bridgeKey = env('HELLOTRANSPORT_BRIDGE_KEY');
        $bridgeUrl = env('HELLOTRANSPORT_BRIDGE_URL', 'https://hellotransport.com');

        if (!$bridgeKey) {
            Log::warning('EmployeeObserver: HELLOTRANSPORT_BRIDGE_KEY not configured');
            return;
        }

        // Split full_name into first/last
        $parts     = explode(' ', trim($employee->full_name ?? ''), 2);
        $firstName = $parts[0] ?: explode('@', $employee->email)[0];
        $lastName  = $parts[1] ?? 'Employee';

        // Get role information
        $role     = $employee->role;
        $roleName = $role ? $role->name : 'Employee';
        $roleId   = $employee->role_id ?? 0;

        // Prepare payload
        $payload = [
            'employee_id' => $employee->id,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'email'       => $employee->email,
            'phone'       => $employee->phone ?? '',
            'role_id'     => $roleId,
            'role_name'   => $roleName,
        ];

        try {
            $response = Http::withHeaders([
                'X-Bridge-Key'  => $bridgeKey,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ])
            ->timeout(10)
            ->post($bridgeUrl . '/bridge/employee/sync', $payload);

            if ($response->successful()) {
                $agentId = $response->json('user_id');

                // Save agent_id back to hr_employees without triggering observer again
                if ($agentId) {
                    \Illuminate\Support\Facades\DB::table('hr_employees')
                        ->where('id', $employee->id)
                        ->update(['agent_id' => $agentId]);
                }

                Log::info('EmployeeObserver: Employee synced to washinton_agent', [
                    'employee_id' => $employee->id,
                    'user_id'     => $agentId,
                    'email'       => $employee->email,
                ]);
            } else {
                Log::warning('EmployeeObserver: Bridge endpoint returned error', [
                    'employee_id' => $employee->id,
                    'email'       => $employee->email,
                    'status'      => $response->status(),
                    'response'    => $response->json(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('EmployeeObserver: Failed to call bridge endpoint', [
                'employee_id' => $employee->id,
                'email'       => $employee->email,
                'bridge_url'  => $bridgeUrl . '/bridge/employee/sync',
                'error'       => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
