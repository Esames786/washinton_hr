<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeBreak;
use App\Models\ShiftType;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class EmployeeBreakController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = EmployeeBreak::with('employee')
                ->select('employee_breaks.id', 'employee_breaks.employee_id', 'employee_breaks.break_start', 'employee_breaks.break_end', 'employee_breaks.break_duration')
                ->where('employee_breaks.employee_id',auth('employee')->id());

            return DataTables::of($data)
                ->addColumn('employee_name', function ($row) {
                    return $row->employee->full_name ?? '-';
                })
                ->editColumn('break_duration', function ($row) {
                    if (!$row->break_duration) {
                        return '-';
                    }

                    // break_duration DB me minutes (decimal) hai
                    $minutes = (float) $row->break_duration;

                    // CarbonInterval banake human readable
                    $interval = CarbonInterval::minutes($minutes)->cascade();

                    // Example: "1 hour 12 minutes (72 mins)"
                    return $interval->forHumans();
                })
                ->filter(function ($query) {
                    if ($search = request('search')['value'] ?? false) {
                        $query->whereHas('employee', function($sub) use ($search) {
                            $sub->where('full_name', 'like', "%{$search}%");
                        });
                    }
                })
                ->make(true);
        }

        return view('employee.breaks');
    }

    public function startBreak(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()
            ], 422);
        }
        $employee = auth('employee')->user();
        $today = now('Asia/Karachi')->toDateString();

        $lastBreak = EmployeeBreak::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->orderByDesc('id')
            ->first();

        if ($lastBreak && is_null($lastBreak->break_end)) {
            return response()->json(['error' => 'You are already on a break.'], 400);
        }

        $break = new EmployeeBreak();
        $break->employee_id = $employee->id;
        $break->break_start = now('Asia/Karachi')->format('H:i:s'); // ✅ second include
        $break->created_by  = $employee->id;
        $break->reason = $request->reason;
        $break->save();

        return response()->json(['success' => 'Break started successfully.']);
    }

    public function endBreak()
    {
        $employee = auth('employee')->user();
        $today = now('Asia/Karachi')->toDateString();

        $lastBreak = EmployeeBreak::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->whereNull('break_end')
            ->orderByDesc('id')
            ->first();

        if (!$lastBreak) {
            return response()->json(['error' => 'No active break found.'], 400);
        }

        $lastBreak->break_end = now('Asia/Karachi')->format('H:i:s'); // save time

        // ✅ Combine date + time for proper diff
        $start = Carbon::parse($lastBreak->created_at->toDateString() . ' ' . $lastBreak->break_start, 'Asia/Karachi');
        $end   = Carbon::parse($lastBreak->created_at->toDateString() . ' ' . $lastBreak->break_end, 'Asia/Karachi');

        $diffInSeconds = $start->diffInSeconds($end);
        $lastBreak->break_duration = round($diffInSeconds / 60, 2); // duration in minutes
        $lastBreak->updated_by = $employee->id;
        $lastBreak->save();

        return response()->json(['success' => 'Break ended successfully.']);
    }



//    public function endBreak()
//    {
//        $employee = auth('employee')->user();
//        $today = now('Asia/Karachi')->toDateString();
//
//        $lastBreak = EmployeeBreak::where('employee_id', $employee->id)
//            ->whereDate('created_at', $today)
//            ->whereNull('break_end')
//            ->orderByDesc('id')
//            ->first();
//
//        if (!$lastBreak) {
//            return response()->json(['error' => 'No active break found.'], 400);
//        }
//
//        $lastBreak->break_end = now('Asia/Karachi')->format('H:i:s');
//        $start = Carbon::createFromFormat('H:i:s', $lastBreak->break_start, 'Asia/Karachi');
//        $end   = Carbon::createFromFormat('H:i:s', $lastBreak->break_end, 'Asia/Karachi');
//        $lastBreak->break_duration = $start->diffInMinutes($end);
//        $lastBreak->updated_by = $employee->id;
//        $lastBreak->save();
//
//        return response()->json(['success' => 'Break ended successfully.']);
//    }

    public static function getBreakStatus()
    {
        $employee = auth('employee')->user();
        $today = now('Asia/Karachi')->toDateString();

        $lastBreak = EmployeeBreak::where('employee_id', $employee->id)
            ->whereDate('created_at', $today)
            ->orderByDesc('id')
            ->first();

        if ($lastBreak && is_null($lastBreak->break_end)) {
            return ['status' => 'on_break', 'break_id' => $lastBreak->id];
        }

        return ['status' => 'available'];
    }

}
