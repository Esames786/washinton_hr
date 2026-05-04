<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAssignLeave;
use App\Models\EmployeeAttendanceRequest;
use App\Models\EmployeeLeave;
use App\Models\EmployeeTicket;
use App\Models\LeaveType;
use App\Models\TicketAttachment;
use App\Models\TicketType;
use App\Traits\AttendanceServiceTrait;
use App\Traits\LeaveServiceTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class EmployeeTicketController extends Controller
{

    use AttendanceServiceTrait,LeaveServiceTrait;

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tickets = EmployeeTicket::with(['ticket_type','employee'])
                ->where('employee_id', auth('employee')->id())
                ->select('id', 'employee_id','ticket_type_id', 'status_id','subject','description', 'created_at');

            return DataTables::of($tickets)
                ->addColumn('ticket_type', fn($row) => $row->ticket_type?->name ?? '')
                ->addColumn('employee_name', fn($row) => $row->employee?->full_name ?? '')
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
                    return '<a href="'.route('employee.tickets.chat', $row->id).'" class="btn btn-outline-info-600 px-20 py-11">Chat Details</a>';
                })
                ->rawColumns(['status','action'])
                ->make(true);
        }

        $ticketTypes = TicketType::where('status', 1)->get();
        $leave_types = LeaveType::where('status', 1)->get();
        return view('employee.tickets.index', compact('ticketTypes','leave_types'));
    }

//    public function store(Request $request)
//    {
//        $request->validate([
//            'ticket_type_id' => 'required|exists:ticket_types,id',
//            'subject'        =>'required|string|max:255',
//        ]);
//
//        $extraData = [];
//
//        // create ticket first (without extra_data)
//        $ticket = EmployeeTicket::create([
//            'employee_id'    => auth('employee')->id(),
//            'ticket_type_id' => $request->ticket_type_id,
//            'subject' => $request->input('subject'),
//            'description' => $request->input('description'),
//            'status_id'      => 1,
//            'created_by'     => auth('employee')->id(),
//        ]);
//
//        if ($request->has('fields')) {
//            foreach ($request->fields as $key => $value) {
//                if ($request->hasFile("fields.$key")) {
//                    $file = $request->file("fields.$key");
//                    $filename = time().'_'.$file->getClientOriginalName();
//                    $file->move(public_path("Uploads/tickets_attachments/{$ticket->id}/"), $filename);
//
//                    $path = "Uploads/tickets_attachments/{$ticket->id}/".$filename;
//
//                    // save reference in extra_data
//                    $extraData[$key] = $path;
//
//                    // save in ticket_attachments table
//                    TicketAttachment::create([
//                        'ticket_id' => $ticket->id,
//                        'file_path' => $path,
//                        'mime_type' => $file->getClientMimeType(),
//                    ]);
//                } else {
//                    $extraData[$key] = $value;
//                }
//            }
//
//            // update ticket with extra_data after loop
//            $ticket->update(['extra_data' => $extraData]);
//        }
//
//        return redirect()->back()->with('success','Ticket created successfully.');
//    }

    public function store(Request $request)
    {
        // Basic validation (always run)
        $validator = Validator::make($request->all(), [
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'subject'        => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticketType = (int) $request->ticket_type_id;

        // Conditional validation rules
        $conditionalRules = [];

        if ($ticketType === 1) {
            $conditionalRules = [
                'date'        => 'required|date',
                'check_in'    => 'required|date_format:H:i',
                'check_out'   => 'required|date_format:H:i',
                'description' => 'required|string|max:1000',
            ];
        } elseif ($ticketType === 2) {
            $conditionalRules = [
                'from_date'   => 'required|date',
                'to_date'     => 'required|date|after_or_equal:from_date',
                'leave_type'  => 'required|integer|exists:leave_types,id',
                'description' => 'required|string|max:1000',
            ];
        }

        // Check conditional validation
        if (!empty($conditionalRules)) {
            $validator = Validator::make($request->all(), $conditionalRules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $employee_id = auth('employee')->id();


           if ($ticketType === 1) {

               // Attendance validation
               $attendanceCheck = $this->validateAttendanceTime(
                   $employee_id,
                   $request->date,
                   $request->check_in
               );

               if (!$attendanceCheck['status']) {
                   DB::rollBack();
                   return response()->json($attendanceCheck, 409);
               }

               // Check duplicate attendance request
               $attendanceCheck = $this->employee_attendance_check(
                   $employee_id,
                   $request->date
               );

               if (!$attendanceCheck['status']) {
                   DB::rollBack();
                   return response()->json($attendanceCheck, 409);
               }
            }

           elseif ($ticketType === 2) {
                    $leaveCheck = $this->employee_leaves_check(
                        $employee_id,
                        $request->leave_type,
                        $request->from_date,
                        $request->to_date
                    );

                    if (!$leaveCheck['status']) {
                        DB::rollBack();
                        return response()->json($leaveCheck, 409);
                    }
           }

            // Create ticket
            $ticket = EmployeeTicket::create([
                'employee_id'    => $employee_id,
                'ticket_type_id' => $ticketType,
                'subject'        => $request->subject,
                'description'    => $request->description,
                'status_id'      => 1,
                'created_by'     => $employee_id,
            ]);

            // Create records based on ticket type
            if ($ticketType === 2) {
                $this->create_employee_leave(
                    $employee_id,
                    $request->leave_type,
                    $request->from_date,
                    $request->to_date,
                    $request->description,
                    $ticket->id,
                    $leaveCheck['data']['requested_days'],
                );
            } elseif ($ticketType === 1) {
                $this->create_employee_attendance_request(
                    $employee_id,
                    $request->date,
                    $request->check_in,
                    $request->check_out,
                    $request->description,
                    $ticket->id
                );
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Ticket created successfully.',
                'data'    => $ticket
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




}
