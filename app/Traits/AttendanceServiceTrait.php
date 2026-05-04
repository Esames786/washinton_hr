<?php

namespace App\Traits;

use App\Exceptions\TraitException;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceRequest;
use App\Models\ShiftType;
use App\Models\ShiftAttendanceRule;
use App\Models\Employee;

trait AttendanceServiceTrait
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
            $check_in = $check_in_time;
            [$status_id, $deduct_percent] = $this->calculateCheckInStatus($attendance_date, $check_in_time, $shift, $rules);

            $attendance->check_in             = $check_in;
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
            $check_out = $check_out_time;
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

            $attendance->check_out             = $check_out;
            $attendance->attendance_status_id  = $result['status_id'];
            $attendance->entry_weight          = $result['entry_weight'];
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

            $overtime = $this->calculateOvertime($attendance, $employee->basic_salary,$shift);
            $attendance->overtime_seconds = $overtime['seconds'];
            $attendance->overtime_amount  = $overtime['amount'];
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
     */
    public function calculateCheckInStatus($attendance_date, $check_in_time, $shift, $rules)
    {
        $timestamp  = strtotime($attendance_date . ' ' . $check_in_time);
        $shiftStart = strtotime($attendance_date . ' ' . $shift->shift_start);
        $shiftEnd   = strtotime($attendance_date . ' ' . $shift->shift_end);

        // Overnight shift alignment
        if ($shiftEnd <= $shiftStart) {
            $shiftEnd += 24 * 3600;
            if ($timestamp < $shiftStart) $timestamp += 24 * 3600;
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
            if ($shiftEnd <= $shiftStart) $currentTime += 24 * 3600;

            $nextTime = $i + 1 < count($timeRules)
                ? strtotime($attendance_date . ' ' . $timeRules[$i + 1]->entry_time)
                : $shiftEnd;

            if ($shiftEnd <= $shiftStart && $i + 1 < count($timeRules)) $nextTime += 24 * 3600;

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
        if ($shiftEndTs <= $shiftStartTs && $ts < $shiftStartTs) $ts += 24 * 3600;
        return $ts;
    }

    /**
     * Calculate check-out status (fixed: seconds preserved, early exit applied)
     */
    public function calculateCheckOutStatus($check_out_time, $attendance, $shift, $rules, float $basic_salary): array
    {
        $attendanceDate = $attendance->attendance_date;

        $checkInTs  = strtotime($attendanceDate . ' ' . $attendance->check_in);
        $checkOutTs = strtotime($attendanceDate . ' ' . $check_out_time);

        $shiftStartTs = strtotime($attendanceDate . ' ' . $shift->shift_start);
        $originalShiftEndTs = strtotime($attendanceDate . ' ' . $shift->shift_end);  // NEW: Track original for detection
        $shiftEndTs   = $originalShiftEndTs;

        $isOvernight = ($shiftEndTs <= $shiftStartTs);

        if ($isOvernight) {
            $shiftEndTs += 24 * 3600;
            if ($checkOutTs < $shiftStartTs) $checkOutTs += 24 * 3600;
            if ($checkInTs < $shiftStartTs) $checkInTs += 24 * 3600;
        }
//        $working_hours = round(($checkOutTs - $checkInTs) / 3600, 2);

        $working_seconds = $checkOutTs - $checkInTs;
        $working_hours = $working_seconds > 0 ? $working_seconds : 0;

        $earlyStatusPriority = [10, 11, 4];

        $earlyRules = $rules
            ->filter(fn($r) => in_array((int)$r->attendance_status_id, $earlyStatusPriority, true) && !empty($r->entry_time))
            ->map(function ($r) use ($attendanceDate, $shiftStartTs, $isOvernight) {
                $ts = strtotime($attendanceDate . ' ' . $r->entry_time);
                if ($isOvernight && $ts < $shiftStartTs) $ts += 24 * 3600;  // CHG: Use flag, no $shiftEndTs check
                return [
                    'status_id'    => (int)$r->attendance_status_id,
                    'entry_weight' => (float)$r->entry_weight,
                    'cutoff_ts'    => $ts
                ];
            })
            ->sort(function ($a, $b) use ($earlyStatusPriority) {
                $pa = array_search($a['status_id'], $earlyStatusPriority, true);
                $pb = array_search($b['status_id'], $earlyStatusPriority, true);
                if ($pa !== $pb) return $pa <=> $pb;
                return $a['cutoff_ts'] <=> $b['cutoff_ts'];
            })
            ->values();

        $checkOutMinuteTs = strtotime(date('Y-m-d H:i', $checkOutTs));

        foreach ($earlyRules as $r) {
            $cutoffMinuteTs = strtotime(date('Y-m-d H:i', $r['cutoff_ts']));
            if ($isOvernight && $cutoffMinuteTs < $shiftStartTs) $cutoffMinuteTs += 24 * 3600;  // CHG: Use flag

            if ($checkOutMinuteTs <= $cutoffMinuteTs) {
                $isEarlyExit = ((int)$r['status_id'] === 4);

                $combined = null;
//                if ($isEarlyExit) {
                    $combined = $this->combineDeductPercentsIfApplicable(
                        $basic_salary,
                        (int)$attendance->attendance_status_id,
                        (float)$attendance->entry_weight,
                        (float)$r['entry_weight']
                    );
//                }

                return [
//                    'status_id'       => $isEarlyExit ? 4 : (int)$r['status_id'],
                    'status_id'       =>  (int)$r['status_id'],
                    'entry_weight'    => (float)$r['entry_weight'],
                    'working_hours'   => $working_hours,
//                    'is_early_exit'   => $isEarlyExit,
                    'is_early_exit'   => 1,
                    'salary_override' => $combined,
                ];
            }
        }

        return [
            'status_id'       => (int)$attendance->attendance_status_id,
            'entry_weight'    => (float)$attendance->entry_weight,
            'working_hours'   => $working_hours,
            'is_early_exit'   => false,
            'salary_override' => null,
        ];
    }

    /**
     * Combine deductions for early exit
     */
    private function combineDeductPercentsIfApplicable(
        float $basic_salary,
        int $checkin_status_id,
        float $checkin_deduct_percent,
        float $early_exit_deduct_percent
    ): ?array {
        $deductableStatuses = [1,2, 3, 9];
        if (!in_array($checkin_status_id, $deductableStatuses, true)) return null;

        $combined_percent = $checkin_deduct_percent + $early_exit_deduct_percent;
        if ($combined_percent > 100) $combined_percent = 100.0;

        [$calc, $deduct] = $this->calcFromDeductPercent($basic_salary, $combined_percent);
        return [
            'calculated' => $calc,
            'deducted'   => $deduct,
        ];
    }

    /**
     * Calculate salary from deduction percent
     */
    public function calcFromDeductPercent($basic_salary, $deduct_percent)
    {
        $dailySalary = $basic_salary / 30;
        $deducted    = round($dailySalary * ($deduct_percent / 100), 2);
        if ($deducted > $dailySalary) $deducted = $dailySalary;
        $calculated  = round($dailySalary - $deducted, 2);
        return [$calculated, $deducted];
    }

    /**
     * Duplicate-check helper
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
     * Request create
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
     * Validate check-in time within shift window
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
            if ($timestamp < $shiftStart) $timestamp += 24 * 3600;
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

    private function calculateOvertime($attendance, $basic_salary, $shift)
    {
        $checkOutTs = strtotime($attendance->check_out);
        $attendanceDate = $attendance->attendance_date;
        $shiftStartTs = strtotime($attendanceDate . ' ' . $shift->shift_start);
        $shiftEndTs   = strtotime($attendanceDate . ' ' . $shift->shift_end);

        // Agar shift midnight cross karti hai (e.g. 16:00–02:00)
        if ($shiftEndTs <= $shiftStartTs) {
            $shiftEndTs = strtotime('+1 day', $shiftEndTs);
        }

        if ($checkOutTs > $shiftEndTs) {
            $overtimeSeconds = $checkOutTs - $shiftEndTs;

            // Sirf tab overtime milega jab kam se kam 5 min (300 sec) rukha ho
            if ($overtimeSeconds > 300) {

                // Sirf poore minutes count honge
                $overtimeMinutes = floor($overtimeSeconds / 60);

                // Wapis seconds me convert karke save karna
                $overtimeSeconds = $overtimeMinutes * 60;

                // Ab shift duration nikalte hain (in hours)
                $shiftHours = ($shiftEndTs - $shiftStartTs) / 3600;

                // Salary calculation
                $basicSalary = $basic_salary;
                if ($shiftHours <= 0) $shiftHours = 8; // fallback default

                $hourlyRate   = $basicSalary / 30 / $shiftHours;
//                $overtimeRate = $hourlyRate * 1.5;
                $overtimeRate = $hourlyRate;


                // Hours nikalne ke liye seconds / 3600
                $overtimeHours  = $overtimeSeconds / 3600;
                $overtimeAmount = round($overtimeHours * $overtimeRate, 2);

                return [
                    'seconds' => $overtimeSeconds, // rounded seconds
                    'amount'  => $overtimeAmount,
                ];
            }
        }

        return [
            'seconds' => 0,
            'amount'  => 0,
        ];
    }

}
