<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeHolidayException;
use App\Models\EmployeeLeave;
use App\Models\EmployeeWorkingDay;
use App\Models\Holiday;
use App\Models\ShiftAttendanceRule;
use App\Models\ProductivityRule;
use App\Traits\AttendanceServiceTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MarkDailyAttendance extends Command
{

    use AttendanceServiceTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily finalize employee attendance at shift end if not marked manually';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // HR portal operates on Pakistan Standard Time (Asia/Karachi). Pin explicitly so the
        // daily finalization + overnight (<6 AM) logic stays correct regardless of app/server tz.
        $now = Carbon::now('Asia/Karachi');
        $today = $now->toDateString();

        try {
            // Get all employees
            $employees = Employee::with('shift')->where('employee_status_id',1)->get();
            $shiftRules = ShiftAttendanceRule::all()->groupBy('shift_type_id');

            // Productivity bands (active-time → attendance status + deduction), highest min first
            $prodRules = ProductivityRule::where('status', 1)->orderByDesc('min_percent')->get();
            $hasActiveTimeTable = Schema::hasTable('agent_active_times');

            foreach ($employees as $employee) {
                $shift = $employee->shift;

                if (!$shift) continue;

                // Parse shift start & end with date
                $shiftStart = Carbon::parse($today . ' ' . $shift->shift_start);
                $shiftEnd   = Carbon::parse($today . ' ' . $shift->shift_end);

//                // Overnight shift handling
//                if ($shift->shift_start > $shift->shift_end) {
//                    $shiftEnd = Carbon::parse($today . ' ' . $shift->shift_end)->addDay();
//                } else {
//                    $shiftEnd = Carbon::parse($today . ' ' . $shift->shift_end);
//                }
//
//                $current = $now->format('Y-m-d H:i');
//                $emp_shift_end = $shiftEnd->copy()->addHour()->addMinutes(5)->format('Y-m-d H:i');
                // Agar abhi subah ke 6 baje se pehle hai -> iska matlab hum abhi pichle din ki shift me hain
                if ($now->hour < 6) {
                    $shiftBaseDate = $now->copy()->subDay()->toDateString();
                } else {
                    $shiftBaseDate = $now->toDateString();
                }

                $shiftStart = Carbon::parse($shiftBaseDate . ' ' . $shift->shift_start);

                if ($shift->shift_start > $shift->shift_end) {
                    // Overnight shift
                    $shiftEnd = Carbon::parse($shiftBaseDate . ' ' . $shift->shift_end)->addDay();
                } else {
                    $shiftEnd = Carbon::parse($shiftBaseDate . ' ' . $shift->shift_end);
                }

                $current       = $now->format('Y-m-d H:i');
                $emp_shift_end = $shiftEnd->copy()->addHour()->addMinutes(5)->format('Y-m-d H:i');

                // Run only after shift end (+3 minutes buffer)
                if ($current < $emp_shift_end) {
                    continue;
                }

                // Existing record for this shift date (overnight-safe via shiftBaseDate)
                $existing = EmployeeAttendance::where('employee_id', $employee->id)
                    ->whereDate('attendance_date', $shiftBaseDate)
                    ->first();

                // Respect a real manual check-in — never override the employee's own punch
                if ($existing && $existing->check_in) {
                    continue;
                }

                // ---- Attendance Finalization Rules ---- //
                $statusId = null;
                $ticket_id =null;

                // The holiday / leave / weekend checks must use the SHIFT date ($shiftBaseDate),
                // not the calendar $today. For an overnight shift finalized after midnight these
                // differ by a day, which previously mis-classified a night-shift worker's
                // leave/holiday/weekend (e.g. marked Absent instead of Leave).
                $checkDate = $shiftBaseDate;

                // 1) Check Holiday
                $holiday = Holiday::where(function ($q) use ($checkDate) {
                    $q->whereDate('holiday_date', $checkDate)
                        ->orWhere(function ($q2) use ($checkDate) {
                            $q2->where('is_recurring', 1)
                                ->whereMonth('month', Carbon::parse($checkDate)->month)
                                ->whereDay('day', Carbon::parse($checkDate)->day);
                        });
                })->where('status', 1)->first();

                if ($holiday) {
                    // Employee holiday exception? -> Absent
                    $exception = EmployeeHolidayException::where('employee_id', $employee->id)
                        ->where('holiday_id', $holiday->id)
                        ->where('status', 1)
                        ->first();

                    if ($exception) {
                        $statusId = 5; // Absent
                    } else {
                        $statusId = 6; // Holiday
                    }
                }

                // 2) Check Leave
                if (!$statusId) {
                    $leave = EmployeeLeave::where('employee_id', $employee->id)
                        ->where('status', 'approved')
                        ->whereDate('start_date', '<=', $checkDate)
                        ->whereDate('end_date', '>=', $checkDate)
                        ->first();

                    if ($leave) {
                        $statusId = 8; // Leave
                        $ticket_id = $leave->ticket_id;
                    }
                }

                // 3) Check Working Day
                if (!$statusId) {
                    $dayName = Carbon::parse($checkDate)->format('l');

                    $workingDay = EmployeeWorkingDay::where('employee_id', $employee->id)
                        ->where('day_of_week', $dayName)
                        ->first();


                    if ($workingDay && $workingDay->is_working == 0) {
                        $statusId = 7; // Weekend
                    }
                }

                // ---- Productivity-based finalization (primary for portal agents) ----
                // For a normal working day that holiday/leave/weekend didn't claim, an
                // agent's daily active (productive) time decides the attendance band.
                $productiveSeconds   = 0;
                $productivePercent   = null;
                $productivityApplied = false;
                $entryWeight         = null;

                if (!$statusId) {
                    $shiftLenSeconds = $shiftEnd->getTimestamp() - $shiftStart->getTimestamp();
                    if ($shiftLenSeconds <= 0) $shiftLenSeconds = 8 * 3600;

                    if ($hasActiveTimeTable && $employee->agent_id) {
                        try {
                            $productiveSeconds = (int) (DB::table('agent_active_times')
                                ->where('user_id', $employee->agent_id)
                                ->whereDate('work_date', $shiftBaseDate)
                                ->value('active_seconds') ?? 0);
                        } catch (\Throwable $e) {
                            $productiveSeconds = 0;
                        }
                    }

                    if ($employee->agent_id && $productiveSeconds > 0 && $prodRules->isNotEmpty()) {
                        $productiveSeconds = min($productiveSeconds, $shiftLenSeconds);
                        $productivePercent = round($productiveSeconds / $shiftLenSeconds * 100, 2);

                        $band = $prodRules->first(fn($r) => $productivePercent >= (float) $r->min_percent);
                        if ($band) {
                            $statusId            = (int) $band->attendance_status_id;
                            $entryWeight         = (float) $band->deduction_percent;
                            $productivityApplied = true;
                        }
                    }
                }

                // Fallback = Absent
                if (!$statusId) {
                    $statusId = 5; // Absent
                }

                // Resolve deduction weight from shift rules when productivity didn't set it
                if ($entryWeight === null) {
                    $rulesForShift = $shiftRules->get($shift->id, collect());
                    $rule = $rulesForShift->firstWhere('attendance_status_id', $statusId);
                    $entryWeight = $rule ? $rule->entry_weight : 0;
                }

                $basicSalary = $employee->basic_salary ?? 0;
                $dailyBasic  = $basicSalary / 30;

                // Save Attendance — use shiftBaseDate so overnight shift records
                // land on the correct shift date, not today's calendar date.
                // Update the existing auto-record if present, else create a new one.
                $attendance = $existing ?: new EmployeeAttendance();
                $attendance->employee_id = $employee->id;
                $attendance->attendance_date = $shiftBaseDate;
                $attendance->attendance_status_id = $statusId;
                $attendance->entry_weight = $entryWeight;

                if ($productivityApplied) {
                    // Proper deduction split so payroll (which sums deducted_salary) is correct
                    $deducted = round($dailyBasic * ($entryWeight / 100), 2);
                    if ($deducted > $dailyBasic) $deducted = $dailyBasic;
                    $attendance->deducted_salary   = $deducted;
                    $attendance->calculated_salary = round($dailyBasic - $deducted, 2);
                    $attendance->productive_seconds = $productiveSeconds;
                    $attendance->productive_percent = $productivePercent;
                    $attendance->remarks = 'Auto-marked by productivity (' . $productivePercent . '% of shift)';
                } else {
                    // Preserve original behaviour for holiday/leave/weekend/absent
                    $weighted = $dailyBasic;
                    if ($entryWeight > 0) $weighted = round($dailyBasic * ($entryWeight / 100), 2);
                    if ($statusId == 5) {
                        $attendance->deducted_salary = $weighted;
                    } else {
                        $attendance->calculated_salary = $weighted;
                    }
                    $attendance->remarks = 'Auto-marked by system (Rule Applied)';
                }

                $attendance->user_type = 1;
                $attendance->created_by = $attendance->created_by ?: 1;

                if($ticket_id) {
                    $attendance->ticket_id = $ticket_id;
                }
                $attendance->save();

//            $this->info("Employee {$employee->id} marked as status {$statusId} for {$today}");
            }

        } catch (\Throwable $th) {
            Log::channel('job_log')->error([
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ]);
        }

//        $this->info('Attendance finalization completed.');
    }
}
