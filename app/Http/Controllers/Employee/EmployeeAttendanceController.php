<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAttendance;
use App\Models\ShiftAttendanceRule;
use App\Models\ShiftType;
use App\Traits\AttendanceServiceTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class EmployeeAttendanceController extends Controller
{

    use AttendanceServiceTrait;

    protected $statusColors = [
        'Present'    => 'bg-success-focus text-success-main',
        'Late'       => 'bg-warning-focus text-warning-main',
        'Half Day'   => 'bg-info-focus text-info-main',
        'Early Exit' => 'bg-warning-focus text-warning-main',
        'Absent'     => 'bg-danger-focus text-danger-main',

        'Holiday'    => 'bg-info-focus text-info-main',         // Light Blue (neutral)
        'Weekend'    => 'bg-primary-focus text-primary-main',   // Blue
        'Leave'      => 'bg-warning-focus text-warning-main',   // Yellow (special case)
    ];
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = EmployeeAttendance::with('attendance_status')
                ->select('employee_attendances.id', 'employee_attendances.attendance_date','employee_attendances.check_in','employee_attendances.check_out','employee_attendances.working_hours','employee_attendances.attendance_status_id')
                ->where('employee_attendances.employee_id',auth('employee')->id());

            if ($request->from_date && $request->to_date) {
                $data->whereBetween('attendance_date', [$request->from_date, $request->to_date]);
            } elseif ($request->from_date) {
                $data->whereDate('attendance_date', '>=', $request->from_date);
            } elseif ($request->to_date) {
                $data->whereDate('attendance_date', '<=', $request->to_date);
            } else {
                // 🔹 Default: current month ki attendance
                $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
                $endOfMonth   = Carbon::now()->endOfMonth()->format('Y-m-d');

                $data->whereBetween('attendance_date', [$startOfMonth, $endOfMonth]);
            }

            return DataTables::of($data)
                ->addColumn('attendance_status_name', function($row) {
                    if ($row->attendance_status) {
                        $status = $row->attendance_status->name;
                        $bgClass = $this->statusColors[$status] ?? 'bg-neutral-focus text-neutral-main';
                        return '<span class="'.$bgClass.' px-24 py-4 rounded-pill fw-medium text-sm">'.$status.'</span>';
                    }
                    return '';
                })
                ->editColumn('working_hours', function($row) {
                    if ($row->working_hours) {
                        $totalSeconds = $row->working_hours;

                        $hours = floor($totalSeconds / 3600);
                        $minutes = floor(($totalSeconds % 3600) / 60);
                        $seconds = $totalSeconds % 60;

                        $result = [];
                        if ($hours > 0) $result[] = $hours . 'h';
                        if ($minutes > 0) $result[] = $minutes . 'm';
                        if ($seconds > 0 && $hours == 0) $result[] = $seconds . 's';

                        return implode(' ', $result);
                    }
                    return '-';
                })
                ->editColumn('check_in', function($row) {
                    return $row->check_in ? $row->check_in : '-';
                })
                ->editColumn('check_out', function($row) {
                    return $row->check_out ? $row->check_out : '-';
                })
                ->filter(function ($query) {
                    if ($search = request('search.value')) {
                        $query->whereHas('attendance_status', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }
                })
                ->rawColumns(['attendance_status_name'])
                ->make(true);
        }


        return view('employee.attendance_list');
    }



    public function markAttendance(Request $request)
    {
        $request->validate([
            'type' => 'required|in:1,2',
        ]);

        try {
            $employee_id = auth('employee')->id();
            $today = now('Asia/Karachi')->toDateString();
            $currentTime = now('Asia/Karachi')->format('H:i:s');

            if ($request->type == 1) {
                $result = $this->saveAttendance($employee_id, $today, $currentTime);
            } else {
                $result = $this->saveAttendance($employee_id, $today, null, $currentTime);
            }

            if ($result['status']) {
                return response()->json($result, 202);
            } else {
                return response()->json($result, 409);
            }

        } catch (\App\Exceptions\TraitException $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ], 409);
        } catch (\Throwable $th) {
            Log::channel('admin_log')->error([
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'data'    => []
            ], 500);
        }

    }


    //old work
//    public function markAttendance(Request $request)
//    {
//        $request->validate([
//            'type' => 'required|in:1,2', // 1 = check-in, 2 = check-out
//        ]);
//
//        $employee = auth('employee')->user(); // or $request->employee_id
//        $today = now('Asia/Karachi')->toDateString();
//        $currentTime = now('Asia/Karachi')->format('H:i:s');
//
//        // Get today's attendance record
//        $attendance = EmployeeAttendance::firstOrNew([
//            'employee_id' => $employee->id,
//            'attendance_date' => $today,
//        ]);
//
//
//        // Fetch shift and rules
//        $shift = ShiftType::find($employee->shift_id);
//        $rules = ShiftAttendanceRule::where('shift_type_id', $shift->id)
//            ->orderBy('entry_time', 'asc')
//            ->get();
//
//        if ($request->type == 1) { // Check-in
//            if ($attendance->check_in) {
//                $shiftStartTime = strtotime($shift->shift_start);
//                $shiftEndTime = strtotime($shift->shift_end);
//                $checkInTime = strtotime($attendance->check_in);
//
//                if ($shiftEndTime <= $shiftStartTime) $shiftEndTime += 24*3600; // overnight
//                if ($checkInTime < $shiftStartTime) $checkInTime += ($shiftEndTime <= $shiftStartTime ? 24*3600 : 0);
//
//                if (strtotime($currentTime) <= $shiftEndTime) {
//                    return response()->json([
//                        'error' => 'You have already checked in during this shift at ' . $attendance->check_in
//                    ], 400);
//                }
//            }
//
//            [$statusId, $weight] = $this->getAttendanceStatusAndWeight($currentTime, $rules, $shift);
//
//            $attendance->check_in = $currentTime;
//            $attendance->attendance_status_id = $statusId;
//            $attendance->entry_weight = $weight;
//            $attendance->calculated_salary = $this->calculateSalary($employee->basic_salary,$weight);
//            $attendance->created_by = $employee->id;
//            $attendance->save();
//
//            return response()->json([
//                'success' => 'Checked in successfully.',
//                'attendance' => $attendance
//            ]);
//        }
//
//        if ($request->type == 2) { // Check-out
//            if (!$attendance->check_in) {
//                return response()->json([
//                    'error' => 'You need to check in first.'
//                ], 400);
//            }
//
//            if ($attendance->check_out) {
//                return response()->json([
//                    'error' => 'You have already checked out today at ' . $attendance->check_out
//                ], 400);
//            }
//
//            // Calculate working hours
//            $checkInTime = strtotime($attendance->check_in);
//            $checkOutTime = strtotime($currentTime);
//            if ($checkOutTime < $checkInTime) $checkOutTime += 24*3600; // handle midnight crossing
//            $workingHours = round(($checkOutTime - $checkInTime) / 3600, 2);
//
////            // Update Half Day / Absent if check-out affects status
////            if ($attendance->attendance_status_id != 5) { // not Absent
////                [$statusId, $weight] = $this->getAttendanceStatusAndWeight($attendance->check_in, $rules, $shift, $checkOutTime);
////                $attendance->attendance_status_id = $statusId;
////                $attendance->entry_weight = $weight;
////            }
//
//            $attendance->check_out = $currentTime;
//            $attendance->working_hours = $workingHours;
//            $attendance->updated_by = $employee->id;
//            $attendance->save();
//
//            return response()->json([
//                'success' => 'Checked out successfully.',
//                'attendance' => $attendance
//            ]);
//        }
//    }
//
//    private function getAttendanceStatusAndWeight($currentTime, $rules, $shift, $overrideTimestamp = null)
//    {
//        $currentTimestamp = $overrideTimestamp ?? strtotime($currentTime);
//
//        // Shift start & end timestamps
//        $shiftStart = strtotime($shift->shift_start);
//        $shiftEnd = strtotime($shift->shift_end);
//        if ($shiftEnd <= $shiftStart) $shiftEnd += 24*3600; // handle overnight shift
//        if ($currentTimestamp < $shiftStart) $currentTimestamp += ($shiftEnd <= $shiftStart) ? 24*3600 : 0;
//
//        // Iterate through rules
//        foreach ($rules as $rule) {
//            $ruleTime = $rule->entry_time ? strtotime($rule->entry_time) : null;
//            if ($ruleTime && $shiftEnd <= $shiftStart) $ruleTime += 24*3600;
//
//            // Case: Check-in before rule time
//            if ($ruleTime && $currentTimestamp <= $ruleTime) {
//                return [$rule->attendance_status_id, $rule->entry_weight];
//            }
//        }
//
//        // After all rules
//        if ($currentTimestamp < $shiftEnd) {
//            // Between last rule & shift end → Half Day
//            $halfDayRule = $rules->first(fn($r) => $r->attendance_status_id == 3); // Half Day rule
//            return [$halfDayRule?->attendance_status_id ?? 3, $halfDayRule?->entry_weight ?? 0.5];
//        }
//
//        // After shift end → Absent
//        $absentRule = $rules->first(fn($r) => $r->attendance_status_id == 5); // Absent rule
//        return [$absentRule?->attendance_status_id ?? 5, $absentRule?->entry_weight ?? 0];
//    }
//
//    private function calculateSalary($salary,$entryWeight)
//    {
//
//        $dailySalary = ($salary/30); // replace with employee daily salary calculation
//        return $dailySalary * $entryWeight;
//    }

    public static function getAttendanceForCard($employee)
    {
        $now = now('Asia/Karachi');
        $today = $now->toDateString();

        $shift = ShiftType::find($employee->shift_id);
        if (!$shift) {
            return [
                'attendanceToday' => null,
                'checkInDisabled' => true,
                'checkOutDisabled' => true
            ];
        }

        // Pehle try karo attendance fetch karne ka normal way
        $attendanceToday = EmployeeAttendance::where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->orderByDesc('id')
            ->first();

        // Agar milta nahi, tab overnight case check karo
        if (!$attendanceToday) {
            $yesterday = $now->copy()->subDay()->toDateString();
            $attendanceToday = EmployeeAttendance::where('employee_id', $employee->id)
                ->where('attendance_date', $yesterday)
                ->whereNull('check_out')
                ->orderByDesc('id')
                ->first();
        }

        // Base date
        $baseDate = $attendanceToday ? $attendanceToday->attendance_date : $today;

        // Shift start/end based on base date
        $shiftStart = Carbon::parse($baseDate . ' ' . $shift->shift_start, 'Asia/Karachi');
        $shiftEnd   = Carbon::parse($baseDate . ' ' . $shift->shift_end, 'Asia/Karachi');

        // Overnight adjust
        if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
            $shiftEnd->addDay();
        }

        // Defaults
        $checkInDisabled = false;
        $checkOutDisabled = false;

        // RULE 1
        if (!$attendanceToday || !$attendanceToday->check_in) {
            $checkInDisabled = $now->greaterThan($shiftEnd->copy()->addHour());
            $checkOutDisabled = true;
        }
        // RULE 2
        else if ($attendanceToday->check_in && !$attendanceToday->check_out) {
            if ($now->greaterThan($shiftEnd->copy()->addHour())) {
                $checkInDisabled = false;
                $checkOutDisabled = true;
            } else {
                $checkInDisabled = true;
                $checkOutDisabled = false;
            }
        }
        // RULE 3
        else if ($attendanceToday->check_in && $attendanceToday->check_out) {
            $checkInDisabled = !$now->greaterThan($shiftEnd->copy()->addHour());
            $checkOutDisabled = true;
        }

        return [
            'attendanceToday' => $attendanceToday,
            'checkInDisabled' => $checkInDisabled,
            'checkOutDisabled' => $checkOutDisabled
        ];
    }


//    public static function getAttendanceForCard($employee)
//    {
//        $now = now('Asia/Karachi');
//        $today = $now->toDateString();
//        $nowTime = $now->format('H:i:s');
//
//        $shift = ShiftType::find($employee->shift_id);
//        if (!$shift) {
//            return [
//                'attendanceToday' => null,
//                'checkInDisabled' => true,
//                'checkOutDisabled' => true
//            ];
//        }
//
//        // Get attendance of today or overnight one
//        $attendanceToday = EmployeeAttendance::where('employee_id', $employee->id)
//            ->where(function ($query) use ($today, $now, $shiftStart, $shiftEnd) {
//                // Normal same-day attendance
//                $query->where('attendance_date', $today);
//
//                // Overnight shift spanning midnight
//                $query->orWhere(function ($q) use ($today, $shiftEnd, $now) {
//                    $q->where('attendance_date', $now->copy()->subDay()->toDateString())
//                        ->whereNull('check_out') // Still active
//                        ->whereTime('check_in', '<=', $shiftEnd->format('H:i:s'));
//                });
//            })
//            ->orderByDesc('id')
//            ->first();
//
//
//        // Agar attendance mila hai to uski date use karo, warna today
//        $baseDate = $attendanceToday ? $attendanceToday->attendance_date : $today;
//
//        $shiftStart = Carbon::parse($baseDate . ' ' . $shift->shift_start, 'Asia/Karachi');
//        $shiftEnd   = Carbon::parse($baseDate . ' ' . $shift->shift_end, 'Asia/Karachi');
//
//        // Overnight shift adjust
//        if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
//            $shiftEnd->addDay();
//        }
//
//        // Default: both enabled
//        $checkInDisabled = false;
//        $checkOutDisabled = false;
//
//        // RULE 1: Agar check-in hi nahi hai
//        if (!$attendanceToday || !$attendanceToday->check_in) {
//            // Agar abhi shift end time cross ho gaya -> disable check-in
//            if ($now->greaterThan($shiftEnd)) {
//                $checkInDisabled = true;
//            } else {
//                $checkInDisabled = false; // enable till shift end
//            }
//            $checkOutDisabled = true; // no checkout without checkin
//        }
//
//        // RULE 2: Agar check-in hai lekin check-out nahi hai
//        else if ($attendanceToday->check_in && !$attendanceToday->check_out) {
//            if ($now->greaterThan($shiftEnd)) {
//                // Shift end ho gaya -> allow re-check-in
//                $checkInDisabled = false;
//                $checkOutDisabled = true;
//            } else {
//                // Shift end abhi nahi hua -> normal case
//                $checkInDisabled = true;
//                $checkOutDisabled = false;
//            }
//        }
//
//        // RULE 3: Agar check-in bhi hai aur check-out bhi ho chuka hai
//        else if ($attendanceToday->check_in && $attendanceToday->check_out) {
//            // Dobara check-in allow sirf agar shift end ho chuka
//            if ($now->greaterThan($shiftEnd)) {
//                $checkInDisabled = false;
//            } else {
//                $checkInDisabled = true;
//            }
//            $checkOutDisabled = true; // already checked out
//        }
//
//        return [
//            'attendanceToday' => $attendanceToday,
//            'checkInDisabled' => $checkInDisabled,
//            'checkOutDisabled' => $checkOutDisabled
//        ];
//    }


}
