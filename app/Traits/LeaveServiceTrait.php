<?php

namespace App\Traits;


use App\Exceptions\LeaveException;
use App\Exceptions\TraitException;
use App\Models\EmployeeAssignLeave;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait LeaveServiceTrait
{
    private function employee_leaves_check($employee_id, $leave_type_id, $from_date, $to_date,$bypass=true)
    {
        $validLeaves = EmployeeAssignLeave::where('employee_id', $employee_id)
            ->where('status', 1)
            ->whereDate('valid_from', '<=', $from_date)
            ->whereDate('valid_to', '>=', $to_date)
            ->where('leave_type_id', $leave_type_id)
            ->first();

        if (!$validLeaves) {
            return [
                'status'  => false,
                'message' => 'No valid leave quota available for this range.',
                'data'    => []
            ];
        }
        if($bypass) {
            $overlap = EmployeeLeave::where('employee_id', $employee_id)
                ->whereIn('status', [1,2])
                ->where(function($query) use ($from_date, $to_date) {
                    $query->whereBetween('start_date', [$from_date, $to_date])
                        ->orWhereBetween('end_date', [$from_date, $to_date])
                        ->orWhere(function($q) use ($from_date, $to_date) {
                            $q->where('start_date', '<=', $from_date)
                                ->where('end_date', '>=', $to_date);
                        });
                })
                ->exists();

            if ($overlap) {
                return [
                    'status'  => false,
                    'message' => 'You already have leave scheduled during these dates. Please choose a different range.',
                    'data'    => []
                ];
            }
        }

        //Attendance should not exist
        $attendanceExists = EmployeeAttendance::where('employee_id', $employee_id)
            ->whereBetween('attendance_date', [$from_date, $to_date])
            ->exists();

        if ($attendanceExists) {
            return [
                'status'  => false,
                'message' => 'Attendance already exists for one or more requested leave dates.',
                'data'    => []
            ];
        }


        $from = Carbon::parse($from_date);
        $to   = Carbon::parse($to_date);
        $requestedDays   = $to->diffInDays($from) + 1;
        $availableLeaves = $validLeaves->assigned_quota - $validLeaves->used_quota;

        if ($availableLeaves < $requestedDays) {
            return [
                'status'  => false,
                'message' => 'Insufficient leave balance.',
                'data'    => []
            ];
        }

        return [
            'status'  => true,
            'message' => 'Leave request is valid.',
            'data'    => [
                'requested_days' => $requestedDays,
                'valid_leave'    => $validLeaves
            ]
        ];
    }

    private function create_employee_leave($employee_id, $leave_type_id, $from_date, $to_date, $reason, $ticket_id, $requestedDays)
    {
        $employeeLeave = new EmployeeLeave();
        $employeeLeave->employee_id   = $employee_id;
        $employeeLeave->leave_type_id = $leave_type_id;
        $employeeLeave->start_date    = $from_date;
        $employeeLeave->end_date      = $to_date;
        $employeeLeave->total_days    = $requestedDays;
        $employeeLeave->reason        = $reason;
        $employeeLeave->ticket_id     = $ticket_id;
        $employeeLeave->status        = 1;
        $employeeLeave->save();

//        // Update used quota
//        $validLeave->used_quota += $requestedDays;
//        $validLeave->save();
    }

    private function assign_leaves_calculation($employee_id, $leave_type_id, $from_date, $to_date, $total_leaves)
    {
        $validLeaves = EmployeeAssignLeave::where('employee_id', $employee_id)
            ->where('status', 1)
            ->whereDate('valid_from', '<=', $from_date)
            ->whereDate('valid_to', '>=', $to_date)
            ->where('leave_type_id', $leave_type_id)
            ->first();

        if ($validLeaves) {
            $validLeaves->used_quota += $total_leaves;
            $validLeaves->save();
        } else {
            throw new TraitException("Employee assign Leave not found.");
        }
    }
}
