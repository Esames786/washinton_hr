<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\DailyActivityField;
use App\Models\Role;
use App\Models\RoleActivityField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class DailyActivityController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DailyActivityField::select('id', 'name', 'field_type', 'is_required', 'status');

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex justify-content-center gap-2">
                                 <a href='.route('admin.daily_activity_fields.assign_roles_index',['id'=> $row->id]).' class="btn btn-outline-primary-600 radius-8 px-20 py-11">Assign to Role</a>
                                <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn" data-id="'.$row->id.'">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </button></div>';
//                    <button type="button" class="bg-danger-focus text-danger-600 bg-hover-danger-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle delete_btn" data-id="'.$row->id.'">
//                                    <iconify-icon icon="lucide:trash-2" class="menu-icon"></iconify-icon>
//                                </button>
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<button class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm active_btn" data-id="'.$row->id.'">Active</button>'
                        : '<button class="bg-neutral-200 text-neutral-600 border border-neutral-400 px-24 py-4 radius-4 fw-medium text-sm inactive_btn" data-id="'.$row->id.'">Inactive</button>';
                })
                ->editColumn('is_required',function ($row) {
                    if($row->is_required == 1){
                        return 'Yes';
                    }
                    return 'No';
                })
                ->filter(function ($query) {
                    if (request()->has('search') && request('search')['value'] != '') {
                        $search = request('search')['value'];
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('field_type', 'like', "%{$search}%");
                    }
                })
                ->rawColumns(['status', 'action','is_required'])
                ->make(true);
        }
        $roles = Role::where('id','!=',1)->where('status',1)->where('guard_name','employee')->get();
        return view('admin.employee_settings.daily_activity_fields',compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'field_type' => 'required|in:text,textarea,number,date,time,select,file',
            'is_required' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        $field = new DailyActivityField();
        $field->name = $request->name;
        $field->field_type = $request->field_type;
        $field->is_required = $request->is_required;
        $field->status = $request->status;
        $field->created_by = auth()->id();
        $field->save();

        session()->flash('success', 'Daily Activity Field added successfully.');
        return redirect()->back();
    }

    public function edit(DailyActivityField $daily_activity_field)
    {
        return response()->json($daily_activity_field);
    }

    public function update(Request $request, DailyActivityField $daily_activity_field)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'field_type' => 'required|in:text,textarea,number,date,time,select,file',
            'is_required' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        $daily_activity_field->name = $request->name;
        $daily_activity_field->field_type = $request->field_type;
        $daily_activity_field->is_required = $request->is_required;
        $daily_activity_field->status = $request->status;
        $daily_activity_field->updated_by = auth()->id();
        $daily_activity_field->save();

        session()->flash('success', 'Daily Activity Field updated successfully.');
        return redirect()->back();
    }

    public function destroy(DailyActivityField $daily_activity_field)
    {
        $daily_activity_field->delete();
        return response()->json(['success' => 'Daily Activity Field deleted successfully.']);
    }

    public function assign_roles_index($id)
    {

        $daily_activity = DailyActivityField::find($id);
        if($daily_activity) {
            $roles =  Role::where('guard_name','employee')->where('status', 1)->get();
            $assignedRoleIds = RoleActivityField::where('activity_field_id', $id)
                ->pluck('role_id')
                ->toArray();
            return view('admin.employee_settings.activity_fields_assign_role')->with(['roles'=>$roles,'id'=>$id,'daily_activity'=>$daily_activity,'assignedRoleIds'=>$assignedRoleIds]);
        }
        return view('admin.employee_settings.daily_activity_fields')->with([['errors','Role not found']]);
    }

    public function assign_roles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'integer|exists:hr_roles,id',
        ]);

        try {

            DB::beginTransaction();

            $daily_activity = DailyActivityField::where('id',$id)->where('status',1)->first();
            if($daily_activity)
            {
                // delete any existing mapping of this role
//                RoleActivityField::whereIn('role_id',$request->roles)->delete();

                // delete any existing mapping of this commission
                RoleActivityField::where('activity_field_id', $id)->delete();

                $insertData = [];
                foreach ($request->roles as $roleId) {

                    $insertData[] = [
                        'role_id' => $roleId,
                        'activity_field_id' => $id,
                        'created_by' => auth('admin')->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($insertData)) {
                    RoleActivityField::insert($insertData);
                }

                DB::commit();
                return redirect()
                    ->route('admin.daily_activity_fields.index')
                    ->with('success', 'Roles assigned successfully.');

            }

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('admin_log')->error([
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ]);
            return redirect()
                ->back()
                ->with('error', 'Something went wrong while assigning roles.');
        }
    }
}
