<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        return view('admin.employee_settings.leave_types');
    }

    public function list()
    {
        $types = LeaveType::orderBy('name')->get(['id', 'name', 'description', 'is_paid', 'status']);
        return response()->json(['data' => $types]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['created_by'] = auth('admin')->id();
        $type = LeaveType::create($data);
        return response()->json(['success' => true, 'type' => $type]);
    }

    public function update(Request $request, LeaveType $leave_type)
    {
        $data = $this->validateData($request, $leave_type->id);
        $data['updated_by'] = auth('admin')->id();
        $leave_type->update($data);
        return response()->json(['success' => true, 'type' => $leave_type]);
    }

    public function destroy(LeaveType $leave_type)
    {
        // Prevent delete if this leave type is assigned to employees
        $inUse = \Illuminate\Support\Facades\DB::table('hr_employee_assign_leaves')
            ->where('leave_type_id', $leave_type->id)->exists();
        if ($inUse) {
            return response()->json(['success' => false, 'message' => 'Cannot delete — this leave type is assigned to employees.'], 422);
        }
        $leave_type->delete();
        return response()->json(['success' => true]);
    }

    private function validateData(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'name'        => 'required|string|max:75|unique:hr_leave_types,name' . ($ignoreId ? ',' . $ignoreId : ''),
            'description' => 'nullable|string|max:500',
            'is_paid'     => 'nullable|boolean',
            'status'      => 'nullable|boolean',
        ]);
    }
}
