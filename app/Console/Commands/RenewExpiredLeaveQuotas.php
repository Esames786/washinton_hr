<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeAssignLeave;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RenewExpiredLeaveQuotas extends Command
{
    protected $signature = 'leave:renew-quotas';
    protected $description = 'Auto-renew expired leave quotas for active employees';

    public function handle(): void
    {
        $today = Carbon::today();
        $renewed = 0;
        $skipped = 0;

        try {
            // Find all active quota records that have expired
            $expiredQuotas = EmployeeAssignLeave::where('status', 1)
                ->whereDate('valid_to', '<', $today)
                ->get();

            foreach ($expiredQuotas as $quota) {
                // Skip if employee is not active
                $employee = Employee::find($quota->employee_id);
                if (!$employee || (int) $employee->employee_status_id !== 1) {
                    $skipped++;
                    continue;
                }

                // New period: same duration as expired quota
                $oldFrom  = Carbon::parse($quota->valid_from);
                $oldTo    = Carbon::parse($quota->valid_to);
                $duration = $oldFrom->diffInDays($oldTo); // e.g. 364 for a full year

                $newFrom = $oldTo->copy()->addDay();           // day after expiry
                $newTo   = $newFrom->copy()->addDays($duration); // same length

                // Skip if a quota for this employee + leave_type already covers the new period
                $alreadyExists = EmployeeAssignLeave::where('employee_id', $quota->employee_id)
                    ->where('leave_type_id', $quota->leave_type_id)
                    ->whereDate('valid_from', $newFrom->toDateString())
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                // Mark old quota as expired
                $quota->status = 0;
                $quota->save();

                // Create renewed quota — same assigned_quota, used_quota reset to 0
                EmployeeAssignLeave::create([
                    'employee_id'    => $quota->employee_id,
                    'leave_type_id'  => $quota->leave_type_id,
                    'assigned_quota' => $quota->assigned_quota,
                    'used_quota'     => 0,
                    'valid_from'     => $newFrom->toDateString(),
                    'valid_to'       => $newTo->toDateString(),
                    'status'         => 1,
                    'created_by'     => null,
                ]);

                $renewed++;

                Log::info('[LeaveRenew] Quota renewed', [
                    'employee_id'   => $quota->employee_id,
                    'leave_type_id' => $quota->leave_type_id,
                    'new_from'      => $newFrom->toDateString(),
                    'new_to'        => $newTo->toDateString(),
                    'quota'         => $quota->assigned_quota,
                ]);
            }

            Log::info('[LeaveRenew] Done', ['renewed' => $renewed, 'skipped' => $skipped]);

        } catch (\Throwable $e) {
            Log::error('[LeaveRenew] Failed: ' . $e->getMessage());
        }
    }
}
