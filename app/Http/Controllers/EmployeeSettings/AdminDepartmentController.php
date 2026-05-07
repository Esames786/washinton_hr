<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AdminDepartmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $departments = Department::select(['id', 'name', 'status']);
            return DataTables::of($departments)
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
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('admin.employee_settings.departments');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:hr_departments,name',
        ]);

        $department = new Department();
        $department->name   = $request->name;
        $department->status = 1; // default active
        $department->save();

        return redirect()->back()->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return response()->json($department);
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name'   => 'required|unique:hr_departments,name,' . $department->id,
            'status' => 'required|integer|in:1,0',
        ]);

        $department->name   = $request->name;
        $department->status = $request->status;
        $department->save();

        return redirect()->back()->with('success', 'Department updated successfully.');
    }
}
