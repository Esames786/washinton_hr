<?php

namespace App\Traits;

use App\Exceptions\TraitException;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceRequest;
use App\Models\ShiftType;
use App\Models\ShiftAttendanceRule;
use App\Models\Employee;

trait AttendanceServiceTrait_mh
{
    /**
     * Save or update attendance (check-in / check-out)
     */
    public function saveAttendance($employee_id, $attendance_date, $check_in_time = null, $check_out_time = null, $ticket_id = null)
    {
        $employee = Employee::find($employee_id);
        if (!$employee) {
            return [
                'status'  => false,
                'message' => 'Employee not found.',
                'data'    => []
            ];
        }

        $shift = ShiftType::find($employee->shift_id);
        if (!$shift) {
            return [
                'status'  => false,
                'message' => 'Shift not found for employee.',
                'data'    => []
            ];
        }

        $attendance = EmployeeAttendance::firstOrNew([
            'employee_id'     => $employee_id,
            'attendance_date' => $attendance_date,
        ]);

        // Overnight shift check
        $shiftStartTs = strtotime($attendance_date . ' ' . $shift->shift_start);
        $shiftEndTs   = strtotime($attendance_date . ' ' . $shift->shift_end);
        if ($shiftEndTs <= $shiftStartTs) {
            // Overnight shift
            $yesterday = date('Y-m-d', strtotime($attendance_date . ' -1 day'));
            $attendanceYesterday = EmployeeAttendance::where('employee_id', $employee_id)
                ->where('attendance_date', $yesterday)
                ->whereNull('check_out')
                ->first();
            if ($attendanceYesterday && $check_out_time) {
                $attendance = $attendanceYesterday; // Use yesterday's record
            }
        }

        $rules = ShiftAttendanceRule::where('shift_type_id', $shift->id)
            ->orderBy('entry_time', 'asc')
            ->get();

        if ($rules->isEmpty()) {
            return [
                'status'  => false,
                'message' => 'Shift Attendance rule not found for employee.',
                'data'    => []
            ];
        }

        // -------------------- CHECK-IN --------------------
        if ($check_in_time) {
            [$status_id, $deduct_percent] = $this->calculateCheckInStatus($attendance_date, $check_in_time, $shift, $rules);

            $attendance->check_in             = $check_in_time;
            $attendance->attendance_status_id = $status_id;
            $attendance->entry_weight         = $deduct_percent; // store as DEDUCTION %

            [$calc, $deduct] = $this->calcFromDeductPercent($employee->basic_salary, $deduct_percent);
            $attendance->calculated_salary    = $calc;
            $attendance->deducted_salary      = $deduct;

            $attendance->ticket_id            = $ticket_id;
            $attendance->created_by           = $employee_id;
            $attendance->save();

            if (!$check_out_time) {
                return [
                    'status'  => true,
                    'message' => 'Checked in successfully.',
                    'data'    => ['attendance' => $attendance]
                ];
            }
        }

        // -------------------- CHECK-OUT --------------------
        if ($check_out_time) {
            if (!$attendance->check_in) {
                return [
                    'status'  => false,
                    'message' => 'Check-in required before check-out.',
                    'data'    => []
                ];
            }

            $result = $this->calculateCheckOutStatus(
                $check_out_time,
                $attendance,
                $shift,
                $rules,
                (float)$employee->basic_salary
            );

            $attendance->check_out             = $check_out_time;
            $attendance->attendance_status_id  = $result['status_id'];
            $attendance->entry_weight          = $result['entry_weight']; // still DEDUCTION %
            $attendance->working_hours         = $result['working_hours'];
            $attendance->is_early_exit         = $result['is_early_exit'] ? 1 : 0;

            if ($result['salary_override']) {
                $attendance->calculated_salary = $result['salary_override']['calculated'];
                $attendance->deducted_salary   = $result['salary_override']['deducted'];
            } else {
                [$calc, $deduct]               = $this->calcFromDeductPercent($employee->basic_salary, $result['entry_weight']);
                $attendance->calculated_salary = $calc;
                $attendance->deducted_salary   = $deduct;
            }

            $attendance->updated_by            = $employee_id;
            $attendance->save();

            return [
                'status'  => true,
                'message' => 'Checked out successfully.',
                'data'    => ['attendance' => $attendance]
            ];
        }
    }

    /**
     * Determine check-in status & deduction percent based on time rules
     * (Present/Late/Half Day/Quarter) – IDs: 1,2,3,9
     * entry_weight is treated as DEDUCTION %
     */
    public function calculateCheckInStatus($attendance_date, $check_in_time, $shift, $rules)
    {
        $timestamp  = strtotime($attendance_date . ' ' . $check_in_time);
        $shiftStart = strtotime($attendance_date . ' ' . $shift->shift_start);
        $shiftEnd   = strtotime($attendance_date . ' ' . $shift->shift_end);

        // Overnight shift alignment
        if ($shiftEnd <= $shiftStart) {
            $shiftEnd += 24 * 3600;
            if ($timestamp < $shiftStart) {
                $timestamp += 24 * 3600;
            }
        }

        if ($timestamp < $shiftStart || $timestamp > $shiftEnd) {
            throw new TraitException("Check-in time is outside your shift hours");
        }

        $timeRules = $rules->filter(
            fn($r) => in_array((int)$r->attendance_status_id, [1,2,3,9], true)
        )->sortBy('entry_time')->values();

        for ($i = 0; $i < count($timeRules); $i++) {
            $currentRule = $timeRules[$i];
            $currentTime = strtotime($attendance_date . ' ' . $currentRule->entry_time);
            if ($shiftEnd <= $shiftStart) {
                $currentTime += 24 * 3600;
            }

            $nextTime = $i + 1 < count($timeRules)
                ? strtotime($attendance_date . ' ' . $timeRules[$i + 1]->entry_time)
                : $shiftEnd;

            if ($shiftEnd <= $shiftStart && $i + 1 < count($timeRules)) {
                $nextTime += 24 * 3600;
            }

            if ($timestamp >= $currentTime && $timestamp < $nextTime) {
                return [
                    (int)$currentRule->attendance_status_id,
                    (float)$currentRule->entry_weight  // DEDUCTION %
                ];
            }
        }

        $halfDayRule = $timeRules->first(fn($r) => (int)$r->attendance_status_id === 3);
        if ($halfDayRule) {
            return [(int)$halfDayRule->attendance_status_id, (float)$halfDayRule->entry_weight];
        }

        throw new TraitException("No valid check-in rule found (Late/Present/HalfDay/Quarter)");
    }

    /**
     * Align cutoff to shift window (overnight safe)
     */
    private function alignToShiftWindow(string $attendanceDate, string $cutoff, int $shiftStartTs, int $shiftEndTs): int
    {
        $ts = strtotime($attendanceDate . ' ' . $cutoff);
        if ($shiftEndTs <= $shiftStartTs && $ts < $shiftStartTs) {
            $ts += 24 * 3600;
        }
        return $ts;
    }

    /**
     * Check-out logic:
     * - Evaluate Early Quarter (11), Early Halfday (10), Early Exit (4)
     * - entry_weight for these is also DEDUCTION %
     * - Early Exit stacks with check-in deduction if initial status was Late/HalfDay/Quarter
     */
    public function calculateCheckOutStatus($check_out_time, $attendance, $shift, $rules, float $basic_salary): array
    {
        $attendanceDate = $attendance->attendance_date;

        $checkInTs  = strtotime($attendanceDate . ' ' . $attendance->check_in);
        $checkOutTs = strtotime($attendanceDate . ' ' . $check_out_time);
        if ($checkOutTs < $checkInTs) {
            $checkOutTs += 24 * 3600;
        }

        $working_hours = round(($checkOutTs - $checkInTs) / 3600, 2);

        $shiftStartTs = strtotime($attendanceDate . ' ' . $shift->shift_start);
        $shiftEndTs   = strtotime($attendanceDate . ' ' . $shift->shift_end);
        if ($shiftEndTs <= $shiftStartTs) {
            $shiftEndTs += 24 * 3600;
        }

        // Priority by severity: Early Quarter → Early Halfday → Early Exit
        $earlyStatusPriority = [11, 10, 4];

        $earlyRules = $rules
            ->filter(fn($r) => in_array((int)$r->attendance_status_id, $earlyStatusPriority, true) && !empty($r->entry_time))
            ->map(function ($r) use ($attendanceDate, $shiftStartTs, $shiftEndTs) {
                return [
                    'status_id'     => (int)$r->attendance_status_id,
                    'entry_weight'  => (float)$r->entry_weight, // DEDUCTION %
                    'cutoff_ts'     => $this->alignToShiftWindow($attendanceDate, $r->entry_time, $shiftStartTs, $shiftEndTs),
                ];
            })
            ->sort(function ($a, $b) use ($earlyStatusPriority) {
                $pa = array_search($a['status_id'], $earlyStatusPriority, true);
                $pb = array_search($b['status_id'], $earlyStatusPriority, true);
                if ($pa !== $pb) return $pa <=> $pb;
                return $a['cutoff_ts'] <=> $b['cutoff_ts'];
            })
            ->values();

        // minute-level comparison to be consistent
        $checkOutTs = strtotime(date('Y-m-d H:i', $checkOutTs));

        foreach ($earlyRules as $r) {
            $cutoffTs = strtotime(date('Y-m-d H:i', $r['cutoff_ts']));
            if ($checkOutTs <= $cutoffTs) {
                $isEarlyExit = ((int)$r['status_id'] === 4);

                if ($isEarlyExit) {
                    // Stack DEDUCTION % if initial status was one of: Late(2), Half Day(3), Quarter(9)
                    $combined = $this->combineDeductPercentsIfApplicable(
                        $basic_salary,
                        (int)$attendance->attendance_status_id,
                        (float)$attendance->entry_weight,   // DEDUCTION % from check-in
                        (float)$r['entry_weight']           // DEDUCTION % from early exit
                    );

                    if ($combined) {
                        return [
                            'status_id'       => 4,
                            'entry_weight'    => (float)$r['entry_weight'],  // keep the Early Exit % in the row
                            'working_hours'   => $working_hours,
                            'is_early_exit'   => true,
                            'salary_override' => $combined,                   // combined deduction applied to salary
                        ];
                    }
                }

                // Early Quarter / Early Halfday just override (no stacking)
                return [
                    'status_id'       => (int)$r['status_id'],
                    'entry_weight'    => (float)$r['entry_weight'], // DEDUCTION %
                    'working_hours'   => $working_hours,
                    'is_early_exit'   => $isEarlyExit,
                    'salary_override' => null,
                ];
            }
        }

        // No early rule matched → keep check-in values
        return [
            'status_id'       => (int)$attendance->attendance_status_id,
            'entry_weight'    => (float)$attendance->entry_weight, // DEDUCTION %
            'working_hours'   => $working_hours,
            'is_early_exit'   => false,
            'salary_override' => null,
        ];
    }

    /**
     * If check-in was Late/Halfday/Quarter, and checkout is Early Exit,
     * combine deductions: combined% = min(100, checkin% + earlyExit%)
     * Then compute salary from that combined deduction%.
     */
    private function combineDeductPercentsIfApplicable(
        float $basic_salary,
        int $checkin_status_id,
        float $checkin_deduct_percent,
        float $early_exit_deduct_percent
    ): ?array {
        $deductableStatuses = [2, 3, 9]; // Late, Half Day, Quarter

        if (!in_array($checkin_status_id, $deductableStatuses, true)) {
            return null;
        }

        $combined_percent = $checkin_deduct_percent + $early_exit_deduct_percent;
        if ($combined_percent > 100) {
            $combined_percent = 100.0;
        }

        [$calc, $deduct] = $this->calcFromDeductPercent($basic_salary, $combined_percent);
        return [
            'calculated' => $calc,
            'deducted'   => $deduct,
        ];
    }

    /**
     * Core salary math when entry_weight is a DEDUCTION %
     * daily = basic/30
     * deducted = daily * (p/100)
     * calculated = daily - deducted
     */
    public function calcFromDeductPercent($basic_salary, $deduct_percent)
    {
        $dailySalary = $basic_salary / 30;
        $deducted    = round($dailySalary * ($deduct_percent / 100), 2);
        if ($deducted > $dailySalary) {
            $deducted = $dailySalary;
        }
        $calculated  = round($dailySalary - $deducted, 2);
        return [$calculated, $deducted];
    }

    /**
     * Duplicate-check helper (unchanged)
     */
    public function employee_attendance_check($employee_id, $attendance_date)
    {
        $existing = EmployeeAttendanceRequest::where('employee_id', $employee_id)
            ->whereHas('ticket', function ($query) {
                $query->whereNotIn('status_id', [3,4]);
            })
            ->where('attendance_date', $attendance_date)
            ->first();

        if ($existing) {
            return [
                'status'  => false,
                'message' => 'You already have an attendance request for this date.',
                'data'    => []
            ];
        }

        return [
            'status'  => true,
            'message' => 'No duplicate attendance request found.',
            'data'    => []
        ];
    }

    /**
     * Request create (unchanged)
     */
    public function create_employee_attendance_request($employee_id, $date, $check_in, $check_out, $remarks, $ticket_id)
    {
        $attendanceRequest = new EmployeeAttendanceRequest();
        $attendanceRequest->employee_id     = $employee_id;
        $attendanceRequest->attendance_date = $date;
        $attendanceRequest->check_in        = $check_in;
        $attendanceRequest->check_out       = $check_out;
        $attendanceRequest->remarks         = $remarks;
        $attendanceRequest->ticket_id       = $ticket_id;
        $attendanceRequest->created_by      = $employee_id;
        $attendanceRequest->save();
    }

    /**
     * Validate check-in time within shift window (unchanged)
     */
    public function validateAttendanceTime($employee_id, $attendance_date, $check_in_time)
    {
        $shift = ShiftType::find(auth('employee')->user()->shift_id);
        if (!$shift) {
            throw new TraitException("Shift not found for employee.");
        }

        $timestamp  = strtotime($attendance_date . ' ' . $check_in_time);
        $shiftStart = strtotime($attendance_date . ' ' . $shift->shift_start);
        $shiftEnd   = strtotime($attendance_date . ' ' . $shift->shift_end);

        if ($shiftEnd <= $shiftStart) {
            $shiftEnd += 24 * 3600;
            if ($timestamp < $shiftStart) {
                $timestamp += 24 * 3600;
            }
        }

        if ($timestamp < $shiftStart || $timestamp > $shiftEnd) {
            return [
                'status'  => false,
                'message' => "Check-in time is outside your shift hours ({$shift->shift_start} → {$shift->shift_end})",
                'data'    => []
            ];
        }

        return [
            'status'  => true,
            'message' => 'Valid Check-in time.',
            'data'    => []
        ];
    }
}
