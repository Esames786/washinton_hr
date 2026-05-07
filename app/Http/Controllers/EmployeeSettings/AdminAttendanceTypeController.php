<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\AttendanceStatus;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AdminAttendanceTypeController extends Controller
{
    /**
     * Display listing of attendance types.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = AttendanceStatus::select(['id', 'name', 'status']);

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $action = '<div class="d-flex justify-content-center gap-2">
                                 <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn" data-id="'.$row->id.'">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                 </button>
                               </div>';
                    return $action;
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<button class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm active_btn" data-id="'.$row->id.'">Active</button>'
                        : '<button class="bg-neutral-200 text-neutral-600 border border-neutral-400 px-24 py-4 radius-4 fw-medium text-sm inactive_btn" data-id="'.$row->id.'">Inactive</button>';
                })
                ->filter(function ($query) {
                    if (request()->has('search') && request('search')['value'] != '') {
                        $search = request('search')['value'];
                        $query->where('name', 'like', "%{$search}%");
                    }
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('admin.employee_settings.attendance_types');
    }

    /**
     * Store a newly created attendance type.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:hr_attendance_statuses,name',
        ]);

        $attendance = new AttendanceStatus();
        $attendance->name   = $request->name;
        $attendance->status = 1;
        $attendance->save();

        return redirect()->back()->with('success', 'Attendance type created successfully.');
    }

    /**
     * Show attendance type for editing.
     */
    public function edit($id)
    {
        $attendance = AttendanceStatus::findOrFail($id);
        return response()->json($attendance);
    }

    /**
     * Update attendance type.
     */
    public function update(Request $request, $id)
    {
        $attendance = AttendanceStatus::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:hr_attendance_statuses,name,'.$attendance->id,
        ]);

        $attendance->name   = $request->name;
        if ($request->has('status')) {
            $attendance->status = $request->status;
        }
        $attendance->save();

        return redirect()->back()->with('success', 'Attendance type updated successfully.');
    }

    /**
     * Remove attendance type.
     */
    public function destroy($id)
    {
        $attendance = AttendanceStatus::findOrFail($id);
        $attendance->delete();

        return redirect()->back()->with('success', 'Attendance type deleted successfully.');
    }

    /**
     * Toggle status (Active/Inactive).
     */
    public function toggleStatus($id)
    {
        $attendance = AttendanceStatus::findOrFail($id);
        $attendance->status = $attendance->status == 1 ? 0 : 1;
        $attendance->save();

        return response()->json(['status' => 'success']);
    }
}
