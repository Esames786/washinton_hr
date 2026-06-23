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
        $today   = date('Y-m-d');
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
