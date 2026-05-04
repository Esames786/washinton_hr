<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeHolidayException;
use App\Models\EmployeeLeave;
use App\Models\EmployeeWorkingDay;
use App\Models\Holiday;
use App\Models\ShiftAttendanceRule;
use App\Traits\AttendanceServiceTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        $now = Carbon::now();
        $today = $now->toDateString();

        try {
            // Get all employees
            $employees = Employee::with('shift')->where('employee_status_id',1)->get();
            $shiftRules = ShiftAttendanceRule::all()->groupBy('shift_type_id');

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

                // Check if already marked
                $alreadyMarked = EmployeeAttendance::where('employee_id', $employee->id)
                    ->whereDate('attendance_date', $today)
                    ->exists();

                if ($alreadyMarked) {
                    continue;
                }

                // ---- Attendance Finalization Rules ---- //
                $statusId = null;
                $ticket_id =null;

                // 1) Check Holiday
                $holiday = Holiday::where(function ($q) use ($today) {
                    $q->whereDate('holiday_date', $today)
                        ->orWhere(function ($q2) use ($today) {
                            $q2->where('is_recurring', 1)
                                ->whereMonth('month', Carbon::parse($today)->month)
                                ->whereDay('day', Carbon::parse($today)->day);
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
                        ->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today)
                        ->first();

                    if ($leave) {
                        $statusId = 8; // Leave
                        $ticket_id = $leave->ticket_id;
                    }
                }

                // 3) Check Working Day
                if (!$statusId) {
                    $dayName = Carbon::parse($today)->format('l');

                    $workingDay = EmployeeWorkingDay::where('employee_id', $employee->id)
                        ->where('day_of_week', $dayName)
                        ->first();


                    if ($workingDay && $workingDay->is_working == 0) {
                        $statusId = 7; // Weekend
                    }
                }

                // 4) Fallback = Absent
                if (!$statusId) {
                    $statusId = 5; // Absent
                }

                $rulesForShift = $shiftRules->get($shift->id, collect());
                $rule = $rulesForShift->firstWhere('attendance_status_id', $statusId);

                $entryWeight = $rule ? $rule->entry_weight : 0;
                $basicSalary = $employee->basic_salary ?? 0;
                $dailySalary = $basicSalary / 30;
                if($entryWeight > 0) {
                    $dailySalary    = round($dailySalary * ($entryWeight / 100), 2);
                }

                // Save Attendance
                $attendance = new EmployeeAttendance();
                $attendance->employee_id = $employee->id;
                $attendance->attendance_date = $today;
                $attendance->attendance_status_id = $statusId;
                if($statusId == 5) {
                    $attendance->deducted_salary = $dailySalary;
                } else {
                    $attendance->calculated_salary = $dailySalary;
                }
                $attendance->entry_weight = $entryWeight;
                $attendance->user_type = 1;
                $attendance->created_by = 1;
                $attendance->remarks = 'Auto-marked by system (Rule Applied)';

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
