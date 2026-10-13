<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\EmployeeStatus;
use App\Models\Role;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmployeeObserver
{
    /**
     * Statuses at which a recruitment-pipeline hire has finished onboarding and should
     * finally get their CRM account. Resolved by name — ids differ between the Hello and
     * CrazyRays databases since the split.
     */
    private const CRM_READY = [
        EmployeeStatus::ONBOARDING,
        EmployeeStatus::ACTIVE,
    ];

    /**
     * Did this employee come through the recruitment pipeline?
     *
     * Only pipeline hires defer their CRM account. An employee an admin creates directly
     * has no application behind it and keeps the original behaviour — account created
     * immediately — because that path is still useful for experienced hires and in-house staff.
     */
    private function isPipelineHire(Employee $employee): bool
    {
        return ! empty($employee->cr_application_id);
    }

    /** Has a pipeline hire reached the point where the CRM account should exist? */
    private function readyForCrmAccount(Employee $employee): bool
    {
        $status = EmployeeStatus::nameFor($employee->employee_status_id);

        return in_array($status, self::CRM_READY, true);
    }

    /**
     * Create the CRM account on demand, bypassing the pipeline gate.
     *
     * Used by the admin "Create CRM account now" action for someone stuck mid-onboarding
     * who needs access today. Public wrapper around the same sync the observer uses, so
     * there is one code path creating accounts, not two.
     */
    public function forceCrmSync(Employee $employee): void
    {
        Log::info('EmployeeObserver: CRM account created manually by admin', [
            'employee_id'       => $employee->id,
            'cr_application_id' => $employee->cr_application_id,
            'status'            => EmployeeStatus::nameFor($employee->employee_status_id),
        ]);

        $this->syncEmployeeToAgent($employee);
    }
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
        // Recruitment-pipeline hires get their CRM account at the END of onboarding, not
        // here — see readyForCrmAccount() in updated(). Admin-created employees are
        // unaffected and still get theirs immediately.
        if ($this->isPipelineHire($employee)) {
            Log::info('EmployeeObserver: pipeline hire, CRM account deferred until onboarding', [
                'employee_id'       => $employee->id,
                'cr_application_id' => $employee->cr_application_id,
            ]);

            return;
        }

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
        // Tell a pipeline hire where they now stand — email triggers 4, 5 and 6.
        // Pipeline hires only: an ordinary status edit on one of the existing agents
        // should not send them an onboarding email out of nowhere.
        if ($this->isPipelineHire($employee) && $employee->isDirty('employee_status_id')) {
            \App\Support\AgentStageNotifier::notify($employee);
        }

        // A pipeline hire reaching Onboarding/Active is the moment their CRM account is
        // created. Guarded on isDirty so it fires on the transition, not on every later save.
        if ($this->isPipelineHire($employee)
            && $employee->isDirty('employee_status_id')
            && $this->readyForCrmAccount($employee)) {
            try {
                Log::info('EmployeeObserver: pipeline hire reached onboarding, creating CRM account', [
                    'employee_id' => $employee->id,
                    'status'      => EmployeeStatus::nameFor($employee->employee_status_id),
                ]);

                $this->syncEmployeeToAgent($employee);

                return;   // already synced; no need to fall through
            } catch (\Throwable $e) {
                Log::error('EmployeeObserver: Failed to create CRM account at onboarding', [
                    'employee_id' => $employee->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

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
            // B6: pass city so the agent side can auto-assign the matching city panel
            'city'        => $employee->city ?? '',
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
