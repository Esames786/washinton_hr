<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AdminDesignationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $designations = Designation::select(['id', 'name', 'status']);

            return DataTables::of($designations)
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

        return view('admin.employee_settings.designations');
    }

    public function store(Request $request)
    {
        $request->validate([
//            'name' => 'required|unique:designations,name',
            'name' => 'required|string|max:255',
        ]);

        $designation = new Designation();
        $designation->name   = $request->name;
        $designation->status = 1;
        $designation->save();

        return redirect()->back()->with('success', 'Designation created successfully.');
    }

    public function edit(Designation $designation)
    {
        return response()->json($designation);
    }

    public function update(Request $request, Designation $designation)
    {
        $request->validate([
            'name' => 'required|string|max:255',
//            'name'   => 'required|unique:designations,name,' . $designation->id,
            'status' => 'required|integer|in:1,0',
        ]);

        $designation->name   = $request->name;
        $designation->status = $request->status;
        $designation->save();

        return redirect()->back()->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        $designation->delete();
        return response()->json(['success' => 'Designation deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        $designation = Designation::findOrFail($id);
        $designation->status = $designation->status ? 0 : 1;
        $designation->save();

        return response()->json(['success' => 'Status updated successfully.']);
    }
}
