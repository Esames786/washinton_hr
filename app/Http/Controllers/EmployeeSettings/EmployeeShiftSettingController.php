<?php
namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\ShiftAttendanceRule;
use App\Models\ShiftType;
use App\Models\AttendanceStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class EmployeeShiftSettingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ShiftAttendanceRule::with('shift_type', 'attendance_status')
                ->select('id', 'shift_type_id', 'attendance_status_id', 'entry_time', 'entry_weight', 'status');
            return DataTables::of($data)
//                ->addColumn('action', function ($row) {
//                    return  '<div class="d-flex justify-content-center gap-2">
//                                 <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn" data-id="'.$row->id.'">
//                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
//                                </button>
//                              </div>';
//                })
                ->editColumn('entry_time',function ($row){
                    if ($row->entry_time) {
                        return Carbon::parse($row->entry_time)->format('h:i A');
                    }
                   return '-';
                })
                ->addColumn('shift_type_name', fn($row) => $row->shift_type?->name ?? '')
                ->addColumn('attendance_status_name', fn($row) => $row->attendance_status?->name ?? '')
                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<button class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm active_btn" data-id="'.$row->id.'">Active</button>'
                        : '<button class="bg-neutral-200 text-neutral-600 border border-neutral-400 px-24 py-4 radius-4 fw-medium text-sm inactive_btn" data-id="'.$row->id.'">Inactive</button>';
                })
                ->filter(function ($query) {
                    if (request()->has('search') && request('search')['value'] != '') {
                        $search = request('search')['value'];

                        $query->where(function ($q) use ($search) {
                            $q->whereHas('attendance_status', function ($sub) use ($search) {
                                $sub->where('name', 'like', "%{$search}%");
                            })
                                ->orWhereHas('shift_type', function ($sub) use ($search) {
                                    $sub->where('name', 'like', "%{$search}%");
                                });
                        });
                    }
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        $shiftTypes = ShiftType::where('status', 1)->get();
        $attendanceStatuses = AttendanceStatus::where('status', 1)->get();

        return view('admin.employee_settings.shift_rules', compact('shiftTypes', 'attendanceStatuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shift_type_id' => 'required|exists:hr_shift_types,id',
            'attendance_status_id' => 'required|exists:hr_attendance_statuses,id',
            'entry_time' => 'nullable|date_format:H:i',
            'entry_weight' => 'nullable|numeric',
            'status' => 'required|in:0,1'
        ]);

        $shiftRule = new ShiftAttendanceRule();
        $shiftRule->shift_type_id = $request->shift_type_id;
        $shiftRule->attendance_status_id = $request->attendance_status_id;
        $shiftRule->entry_time = $request->entry_time;
        $shiftRule->entry_weight = $request->entry_weight;
//        $shiftRule->status = $request->status;
//        $shiftRule->created_by = auth()->id(); // commission settings style
        $shiftRule->save();

        session()->flash('success', 'Shift Attendance Rule added successfully.');
        return  redirect()->back();
    }

    public function edit(ShiftAttendanceRule $shift_attendance_rule)
    {
        $shift_attendance_rule->load('shift_type', 'attendance_status');
        return response()->json($shift_attendance_rule);
    }

    public function update(Request $request, ShiftAttendanceRule $shift_attendance_rule)
    {
        $request->validate([
            'shift_type_id' => 'required|exists:hr_shift_types,id',
            'attendance_status_id' => 'required|exists:hr_attendance_statuses,id',
            'entry_time' => 'nullable|date_format:H:i',
            'entry_weight' => 'nullable|numeric',
            'status' => 'required|in:0,1'
        ]);

        $shift_attendance_rule->shift_type_id = $request->shift_type_id;
        $shift_attendance_rule->attendance_status_id = $request->attendance_status_id;
        $shift_attendance_rule->entry_time = $request->entry_time;
        $shift_attendance_rule->entry_weight = $request->entry_weight;
//        $shift_attendance_rule->status = $request->status;
//        $shift_attendance_rule->updated_by = auth()->id(); // commission settings style
        $shift_attendance_rule->save();

        session()->flash('success', 'Shift Attendance Rule updated successfully.');
        return   redirect()->back();
    }

    public function destroy(ShiftAttendanceRule $shift_attendance_rule)
    {
        $shift_attendance_rule->delete();
        return response()->json(['success' => 'Shift Attendance Rule deleted successfully.']);
    }
}
