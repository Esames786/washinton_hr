<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceRequest;
use App\Models\EmployeeLeave;
use App\Models\EmployeeTicket;
use App\Models\ShiftAttendanceRule;
use App\Models\TicketType;
use App\Models\Employee;
use App\Traits\AttendanceServiceTrait;
use App\Traits\LeaveServiceTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class AdminTicketController extends Controller
{
    use AttendanceServiceTrait, LeaveServiceTrait;
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tickets = EmployeeTicket::with(['ticket_type', 'employee', 'approvedByAdmin'])
                ->select('id', 'ticket_type_id', 'employee_id', 'status_id', 'subject', 'description', 'approved_by', 'created_at');

            return DataTables::of($tickets)
                ->addColumn('ticket_type', fn($row) => $row->ticket_type?->name ?? '')
                ->addColumn('employee_name', fn($row) => $row->employee?->full_name ?? '')
                ->addColumn('approved_by', fn($row) => $row->approvedByAdmin?->name ?? '-')
                ->editColumn('created_at', fn($row) => $row->created_at->diffForHumans())
                ->addColumn('status', function ($row) {
                    return match($row->status_id) {
                        1 => '<span class="bg-warning-focus text-warning-main px-24 py-4 rounded-pill fw-medium text-sm">Pending</span>',
                        2 => '<span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Approved</span>',
                        3 => '<span class="bg-danger-focus text-danger-main px-24 py-4 rounded-pill fw-medium text-sm">Rejected</span>',
                        4 => '<span class="bg-neutral-focus text-neutral-main px-24 py-4 rounded-pill fw-medium text-sm">Closed</span>',
                        default => '<span class="bg-neutral-focus text-neutral-main px-24 py-4 rounded-pill fw-medium text-sm">-</span>',
                    };
                })
                ->addColumn('action', function ($row) {
                    $action = '<div class="d-flex justify-content-center gap-2">';
                    $action.='<a href="'.route('admin.tickets.chat', $row->id).'" class="btn btn-outline-info-600 px-20 py-11">Chat Details</a>';
                    if($row->status_id == 1){
                        $action .= '
                        <button type="button" class="btn btn-outline-success-600 px-20 py-11 approved_btn" data-id="'.$row->id.'">Approve</button>
                        <button type="button" class="btn btn-outline-danger-600 px-20 py-11 reject_btn" data-id="'.$row->id.'">Reject</button>
                    ';
                    }
                    $action .= '</div>';
                    return $action;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $ticketTypes = TicketType::where('status', 1)->get();
        $employees   = Employee::where('employee_status_id', 1)->get();

        return view('admin.tickets.index',  
        param($m) $m.Value -replace "'hr_employees'", "'employees'"
    );
    }


    public function create()
    {
        $ticketTypes = TicketType::where('status', 1)->get();
        $employees   = Employee::where('employee_status_id', 1)->get();

        return view('admin.tickets.create',  
        param($m) $m.Value -replace "'hr_employees'", "'employees'"
    );
    }

    public function store(Request $request)
    {
        $request->validate([
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'employee_id'    => 'required|exists:employees,id',
        ]);

        $ticket = new EmployeeTicket();
        $ticket->ticket_type_id = $request->ticket_type_id;
        $ticket->employee_id    = $request->employee_id;
        $ticket->status_id      = 1; // Pending
        $ticket->created_by     = auth()->id();
        $ticket->save();

        return redirect()->route('admin.tickets.index')->with('success', 'Ticket created successfully.');
    }

    public function ticketApprove(EmployeeTicket $ticket)
    {
        if ($ticket->status_id != 1) {
            return response()->json([
                'status'  => false,
                'message' => 'Ticket is not pending approval.',
                'data'    => []
            ], 409);
        }

        DB::beginTransaction();
        try {
            $admin_id = auth('admin')->id();

            // Attendance Ticket
            if ($ticket->ticket_type_id == 1) {
                $attendanceRequest = EmployeeAttendanceRequest::where('ticket_id', $ticket->id)->first();

                if (!$attendanceRequest) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'No attendance request found for this ticket.',
                        'data'    => []
                    ], 404);
                }

                // Save attendance
                $attendanceResult = $this->saveAttendance(
                    $attendanceRequest->employee_id,
                    $attendanceRequest->attendance_date,
                    $attendanceRequest->check_in,
                    $attendanceRequest->check_out,
                    $ticket->id,
                );

                if (!$attendanceResult['status']) {
                    DB::rollBack();
                    return response()->json($attendanceResult, 409);
                }

                // Leave Ticket
            } elseif ($ticket->ticket_type_id == 2) {
                $leaveRequest = EmployeeLeave::where('ticket_id', $ticket->id)
                    ->where('status', 1)
                    ->first();

                if (!$leaveRequest) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'No leave request found for this ticket.',
                        'data'    => []
                    ], 404);
                }

                // Check validity
                $check = $this->employee_leaves_check(
                    $leaveRequest->employee_id,
                    $leaveRequest->leave_type_id,
                    $leaveRequest->start_date,
                    $leaveRequest->end_date,
                    false
                );

                if (!$check['status']) {
                    DB::rollBack();
                    return response()->json($check, 409);
                }

                // Approve leave
                $leaveRequest->status  = 2;
                $leaveRequest->approved_by = $admin_id;
                $leaveRequest->save();

                // Update leave quota
                $this->assign_leaves_calculation(
                    $leaveRequest->employee_id,
                    $leaveRequest->leave_type_id,
                    $leaveRequest->start_date,
                    $leaveRequest->end_date,
                    $leaveRequest->total_days
                );

//                // ✅ Mark today's attendance if leave date includes today
//                $today = now()->toDateString();
//                if ($today >= $leaveRequest->start_date && $today <= $leaveRequest->end_date) {
//                    $alreadyMarked = EmployeeAttendance::where('employee_id', $leaveRequest->employee_id)
//                        ->whereDate('attendance_date', $today)
//                        ->exists();
//                    if(!$alreadyMarked && $ticket->employee){
//                        $entry_weight = 0;
//                        $employee = $ticket->employee;
//
//                        $shift_rule =  ShiftAttendanceRule::where('shift_type_id',$employee->shift_type_id)->where('attendance_status_id',8)->first();
//                        if($shift_rule){
//                            $entry_weight = $shift_rule->entry_weight;
//                        }
//
//                        $basicSalary = $employee->basic_salary ?? 0;
//                        $dailySalary = $basicSalary / 30;
//                        if($entry_weight > 0) {
//                            $dailySalary  = $dailySalary * ($entry_weight / 100);
//                        }
//
//                        $employeeAttendance = new EmployeeAttendance();
//                        $employeeAttendance->employee_id = $leaveRequest->employee_id;
//                        $employeeAttendance->attendance_date = $today;
//                        $employeeAttendance->attendance_status_id = 8;
//                        $employeeAttendance->ticket_id = $ticket->id;
//                        $employeeAttendance->calculated_salary = round($dailySalary,2);
//                        $employeeAttendance->entry_weight = $entry_weight;
//                        $employeeAttendance->remarks = 'Leave approved via ticket';
//                        $employeeAttendance->created_by = auth('admin')->id();
//                        $employeeAttendance->user_type =1;
//                        $employeeAttendance->save();
//                    }
//                }
            }

            // Approve ticket
            $ticket->status_id   = 2;
            $ticket->approved_by = $admin_id;
            $ticket->approved_at = Carbon::now()->toDateString();
            $ticket->save();

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Ticket approved successfully.',
                'data'    => []
            ], 202);

        } catch (\App\Exceptions\TraitException $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ], 409);
        } catch (\Throwable $th) {
            DB::rollBack();
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


    public function ticketReject(EmployeeTicket $ticket,Request $request)
    {
        if ($ticket->status_id != 1) {
            return response()->json([
                'status'  => false,
                'message' => 'Ticket is not pending approval.',
                'data'    => []
            ], 409);
        }

        DB::beginTransaction();
        try {
            $admin_id = auth('admin')->id();

            if ($ticket->ticket_type_id == 2) {
                // Leave Ticket → reject leave record
                $leaveRequest = EmployeeLeave::where('ticket_id', $ticket->id)
                    ->where('status', 1)
                    ->first();

                if ($leaveRequest) {
                    $leaveRequest->status = 3; // Rejected
                    $leaveRequest->reason = $request->remarks;
                    $leaveRequest->save();
                }
            }

            // Update ticket status
            $ticket->admin_remark = $request->remarks;
            $ticket->status_id = 3; // Rejected
            $ticket->rejected_by = $admin_id;
            $ticket->rejected_at = Carbon::now()->toDateString();
            $ticket->save();

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Ticket rejected successfully.',
                'data'    => []
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
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



    public function changeStatus(Request $request, EmployeeTicket $ticket)
    {
        $request->validate(['status_id' => 'required|in:2,3,4']);
        $ticket->status_id = $request->status_id;
        $ticket->updated_by = auth()->id();
        $ticket->save();

        return response()->json(['success' => 'Status updated successfully']);
    }
}
