<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\AttendanceStatus;
use App\Models\ShiftAttendanceRule;
use App\Models\ShiftType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminShiftTypeController extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $query = ShiftType::all();
            return DataTables::of($query)
                ->addColumn('action', function ($row) {
                    return '
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="bg-success-focus text-success-600
                                bg-hover-success-200 fw-medium w-40-px h-40-px d-flex
                                justify-content-center align-items-center rounded-circle edit_btn"
                                data-id="'.$row->id.'">
                                <iconify-icon icon="lucide:edit"></iconify-icon>
                            </button>
                        </div>';
                })
                ->editColumn('shift_start',function ($row){
                    return Carbon::parse($row->shift_start)->format('h:i A');
                })
                ->editColumn('shift_end',function ($row){
                    return Carbon::parse($row->shift_end)->format('h:i A');
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<button class="bg-success-focus text-success-600 border border-success-main
                            px-24 py-4 radius-4 fw-medium text-sm active_btn" data-id="'.$row->id.'">Active</button>'
                        : '<button class="bg-neutral-200 text-neutral-600 border border-neutral-400
                            px-24 py-4 radius-4 fw-medium text-sm inactive_btn" data-id="'.$row->id.'">Inactive</button>';
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }
        $attendance_status = AttendanceStatus::whereNotIn('id', [2, 5,6,7,8])->get();
        return view('admin.employee_settings.shift_types',compact('attendance_status'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'shift_start' => 'required|date_format:H:i',
            'shift_end'   => 'required|date_format:H:i',
            'attendance_rules.*.entry_time'   => 'required|date_format:H:i',
            'attendance_rules.*.entry_weight' => 'required|integer|min:1|max:100',
        ],[
            'attendance_rules.*.entry_time.required'   => 'Please select an entry time for each attendance rule.',
            'attendance_rules.*.entry_time.date_format' => 'Entry time must be in valid 24-hour format (e.g. 18:15).',

            'attendance_rules.*.entry_weight.required' => 'Please enter an entry weight (1–100).',
            'attendance_rules.*.entry_weight.integer'  => 'Entry weight must be a whole number.',
            'attendance_rules.*.entry_weight.min'      => 'Entry weight cannot be less than 1%.',
            'attendance_rules.*.entry_weight.max'      => 'Entry weight cannot be more than 100%.',
        ]);

        // save shift
        $shift = new ShiftType();
        $shift->name        = $request->name;
        $shift->shift_start = $request->shift_start;
        $shift->shift_end   = $request->shift_end;
        $shift->status      = 1;
        $shift->save();

        // save rules
        $this->saveShiftRules($shift, $request);

        session()->flash('success', 'Shift type created successfully.');
        return redirect()->back();
    }

    public function edit(ShiftType $shift_type)
    {
        // eager load rules
        $shift_type->load('attendanceRules');

        // normalize start/end to HH:MM (no seconds)
        $shift = [
            'id'          => $shift_type->id,
            'name'        => $shift_type->name,
            'shift_start' => substr($shift_type->shift_start, 0, 5),
            'shift_end'   => substr($shift_type->shift_end, 0, 5),
            'status'      => $shift_type->status,
        ];

        // rules keyed by attendance_status_id
        $rules = [];
        foreach ($shift_type->attendanceRules as $r) {
            $rules[$r->attendance_status_id] = [
                'entry_time'   => $r->entry_time ? substr($r->entry_time, 0, 5) : null,
                'entry_weight' => $r->entry_weight,
            ];
        }

        return response()->json([
            'shift' => $shift,
            'rules' => $rules,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'shift_start' => 'required|date_format:H:i',
            'shift_end'   => 'required|date_format:H:i',
            'status'      => 'required|integer|in:1,0',
            'attendance_rules.*.entry_time'   => 'required|date_format:H:i',
            'attendance_rules.*.entry_weight' => 'required|integer|min:1|max:100',

        ]);


        $shift = ShiftType::findOrFail($id);
        $shift->name        = $request->name;
        $shift->shift_start = $request->shift_start;
        $shift->shift_end   = $request->shift_end;
        $shift->status      = $request->status;
        $shift->save();

        // clear old rules & re-insert
        ShiftAttendanceRule::where('shift_type_id', $shift->id)->delete();
        $this->saveShiftRules($shift, $request);

        session()->flash('success', 'Shift type updated successfully.');
        return redirect()->back();
    }


    private function saveShiftRules(ShiftType $shift, Request $request)
    {
        $shiftStart = Carbon::createFromFormat('H:i', $shift->shift_start);

        // ✅ Hardcoded statuses
        $fixed = [
            2 => ['entry_time' => $shiftStart->format('H:i:s'), 'entry_weight' => 0], // Present
            5 => ['entry_time' => null, 'entry_weight' => 100],  // Absent
            6 => ['entry_time' => null, 'entry_weight' => 0],  // Holiday
            7 => ['entry_time' => null, 'entry_weight' => 0],  // Weekend
            8 => ['entry_time' => null, 'entry_weight' => 0],  // Leave
        ];

        foreach ($fixed as $statusId => $rule) {
            ShiftAttendanceRule::create([
                'shift_type_id'        => $shift->id,
                'attendance_status_id' => $statusId,
                'entry_time'           => $rule['entry_time'],
                'entry_weight'         => $rule['entry_weight'],
                'status'               => 1,
            ]);
        }


        // ✅ Dynamic statuses from form
        if ($request->has('attendance_rules')) {
            foreach ($request->attendance_rules as $statusId => $rule) {
                if (!empty($rule['entry_time'])) {
                    ShiftAttendanceRule::create([
                        'shift_type_id'        => $shift->id,
                        'attendance_status_id' => $statusId,
                        'entry_time'           => $rule['entry_time'] . ':00', // safe as HH:MM
                        'entry_weight'         => min(max($rule['entry_weight'] ?? 1, 1), 100),
                        'status'               => 1,
                    ]);
                }
            }
        }
    }


    public function destroy($id)
    {
        $shift = ShiftType::findOrFail($id);
        $shift->delete();
        return response()->json(['success' => 'Shift Type deleted successfully.']);
    }
}
