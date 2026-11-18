<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeActivityController extends Controller
{
    private const MAX_SECONDS_PER_PING = 120;

    /**
     * Record active working seconds for the logged-in employee into the SHARED
     * agent_active_times table (keyed by the linked agent's user id), so active
     * time on the HR portal accumulates into the same daily total as the agent
     * portal. Both apps use the same database.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $employee = auth('employee')->user();
        if (!$employee || !$employee->agent_id || !Schema::hasTable('agent_active_times')) {
            return response()->json(['success' => false]);
        }

        $userId  = (int) $employee->agent_id;
        // Book the time on the SHIFT's date, not the calendar date: before 06:00 (employee's own
        // timezone) is still the shift that started the evening before - the same rule as check-in
        // (EmployeeAttendanceController::markAttendance) and the daily finaliser. On the calendar
        // date, a night shift's after-midnight work counted towards the NEXT day, and a day with no
        // check-in was auto-marked Quarter instead of Absent.
        $now     = now($employee->tz());
        $today   = $now->hour < 6 ? $now->copy()->subDay()->toDateString() : $now->toDateString();
        $seconds = min((int) $request->input('seconds', 0), self::MAX_SECONDS_PER_PING);

        $row = DB::table('agent_active_times')
            ->where('user_id', $userId)->where('work_date', $today)->first();

        if ($seconds >= 1) {
            if ($row) {
                $total = (int) $row->active_seconds + $seconds;
                DB::table('agent_active_times')->where('id', $row->id)
                    ->update(['active_seconds' => $total, 'updated_at' => now()]);
            } else {
                $total = $seconds;
                DB::table('agent_active_times')->insert([
                    'user_id' => $userId, 'work_date' => $today, 'active_seconds' => $total,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        } else {
            $total = $row ? (int) $row->active_seconds : 0;
        }

        $h = intdiv($total, 3600);
        $m = intdiv($total % 3600, 60);

        return response()->json([
            'success'       => true,
            'today_seconds' => $total,
            'today_human'   => ($h > 0 ? $h . 'h ' : '') . $m . 'm',
        ]);
    }
}
