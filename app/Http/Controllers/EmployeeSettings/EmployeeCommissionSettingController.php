<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\CommissionTargetType;
use App\Models\CommissionType;
use App\Models\Role;
use App\Models\RoleCommissionSetting;
use Illuminate\Http\Request;
use App\Models\CommissionSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

class EmployeeCommissionSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CommissionSetting::with('commission_type', 'target_type')
                ->select(
                    'id',
                    'title',
                    'description',
                    'value',
                    'status',
                    'commission_type_id',
                    'target_type_id',
                );
            return DataTables::of($data)
//                <a href='.route('admin.commission_settings.assign_roles_index',['id'=> $row->id]).' class="btn btn-outline-primary-600 radius-8 px-20 py-11">Assign to Role</a>

                ->addColumn('action', function ($row) {
                    return  '<div class="d-flex justify-content-center gap-2">
                                 <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn" data-id="'.$row->id.'">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </button>
                              </div>';

                })
                ->addColumn('commission_type', function ($row) {
                    return $row->commission_type?->name ?? '';
                })
                ->addColumn('target_type', function ($row) {
                    return $row->target_type?->name ?? '';
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<button class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm active_btn" data-id="'.$row->id.'">Active</button>'
                        : '<button class="bg-neutral-200 text-neutral-600 border border-neutral-400 px-24 py-4 radius-4 fw-medium text-sm inactive_btn" data-id="'.$row->id.'">Inactive</button>';
                })
                ->filter(function ($query) {
                    if (request()->has('search') && request('search')['value'] != '') {
                        $search = request('search')['value'];
                        $query->where('title', 'like', "%{$search}%");
                    }
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        $commissionTypes = CommissionType::all();
        $targetTypes = CommissionTargetType::all();

        return view('admin.employee_settings.commissions')->with(compact('commissionTypes','targetTypes'));
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'commission_type_id' => 'required|integer',
            'value' => 'required|numeric',
            'target_type_id' => 'nullable|integer',
//            'status' => 'required|boolean'
        ]);

        $commission_setting = new CommissionSetting();
        $commission_setting->title = $request->title;
        $commission_setting->description = $request->description;
        $commission_setting->commission_type_id = $request->commission_type_id;
        $commission_setting->value = $request->value;
        $commission_setting->target_type_id = $request->target_type_id;
//        $commission_setting->status = $request->status;
//        $commission_setting->created_by = Auth::id();
        $commission_setting->save();

        session()->flash('success', 'Commission setting added successfully.');
        return redirect()->back();

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CommissionSetting $commission_setting)
    {
        $commission_setting->load('commission_type', 'target_type');
        return response()->json($commission_setting);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommissionSetting $commission_setting)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'commission_type_id' => 'required|integer',
            'value' => 'required|numeric',
            'target_type_id' => 'nullable|integer',
//            'status' => 'required|boolean'
        ]);

        $commission_setting->title = $request->title;
        $commission_setting->description = $request->description;
        $commission_setting->commission_type_id = $request->commission_type_id;
        $commission_setting->value = $request->value;
        $commission_setting->target_type_id = $request->target_type_id ?: null;
//        $commissionSetting->status = $request->status;
//        $commissionSetting->updated_by = Auth::id();
        $commission_setting->save();

        session()->flash('success', 'Commission setting updated successfully.');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        CommissionSetting::findOrFail($id)->delete();
        return response()->json(['success' => 'Record deleted successfully.']);
    }

    public function assign_roles_index($id)
    {

        $commission_setting = CommissionSetting::find($id);
        if($commission_setting) {
            $roles =  Role::where('guard_name','employee')->where('status', 1)->get();
            $assignedRoleIds = RoleCommissionSetting::where('commission_setting_id', $id)
                ->pluck('role_id')
                ->toArray();
            return view('admin.employee_settings.commission_assign_role')->with(['roles'=>$roles,'id'=>$id,'commission_setting'=>$commission_setting,'assignedRoleIds'=>$assignedRoleIds]);
        }
        return view('admin.employee_settings.commissions')->with([['errors','Role not found']]);
    }

    public function assign_roles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'integer|exists:hr_roles,id',
        ]);

        try {

            DB::beginTransaction();

            $commission_setting = CommissionSetting::where('id',$id)->where('status',1)->first();
            if($commission_setting)
            {
                // delete any existing mapping of this role
                RoleCommissionSetting::whereIn('role_id',$request->roles)->delete();

                // delete any existing mapping of this commission
                RoleCommissionSetting::where('commission_setting_id', $id)->delete();

                $insertData = [];
                foreach ($request->roles as $roleId) {

                    $insertData[] = [
                        'role_id' => $roleId,
                        'commission_setting_id' => $id,
                        'created_by' => auth('admin')->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($insertData)) {
                    RoleCommissionSetting::insert($insertData);
                }

                DB::commit();
                return redirect()
                    ->route('admin.commission_settings.index')
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
