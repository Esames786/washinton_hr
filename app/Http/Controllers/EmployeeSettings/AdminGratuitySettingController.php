<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\GratuitySetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleGratuitySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class AdminGratuitySettingController extends Controller
{
    /**
     * Show all gratuity settings.
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $data = GratuitySetting::select([
                'id', 'title','description','employee_contribution_percentage','company_contribution_percentage',
                'eligibility_years', 'status'
            ]);

            return DataTables::of($data)
//                <a href='.route('admin.gratuity_settings.assign_roles_index',['id'=> $row->id]).' class="btn btn-outline-primary-600 radius-8 px-20 py-11">Assign to Role</a>

                ->addColumn('action', function ($row) {
                    $action = '<div class="d-flex justify-content-center gap-2">
                                 <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </button>

                                 ';
                     $action.= '</div>';
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
                        $query->where('title', 'like', "%{$search}%");
                    }
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        $roles =  Role::where('status', 1)->get();
        return view('admin.employee_settings.gratuities',compact('roles'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.employee_settings.gratuity_settings.create');
    }

    /**
     * Store new gratuity setting.
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required|string|max:255',
            'is_pf' => 'required|boolean',
            'employee_contribution_percentage' => 'required_if:is_pf,true|nullable|numeric',
            'company_contribution_percentage'  => 'required_if:is_pf,true|nullable|numeric',
            'eligibility_years' => 'required_if:is_pf,false|nullable|integer|min:1',
        ], [
            'employee_contribution_percentage.required_if' => 'Employee contribution percentage is required when Provident Fund is enabled.',
            'company_contribution_percentage.required_if'  => 'Company contribution percentage is required when Provident Fund is enabled.',
            'eligibility_years.required_if'                => 'Eligibility years field is required when Provident Fund is disabled.',
            'eligibility_years.min'                        => 'Years of service must be at least 1.',

        ]);

        $gratuitySetting = new GratuitySetting();
        $gratuitySetting->title = $request->title;
        $gratuitySetting->description = $request->description;
        if($request->is_pf){
            $gratuitySetting->employee_contribution_percentage = $request->employee_contribution_percentage;
            $gratuitySetting->company_contribution_percentage = $request->company_contribution_percentage;
            $gratuitySetting->eligibility_years=0;
        } else {
            $gratuitySetting->employee_contribution_percentage = 0;
            $gratuitySetting->company_contribution_percentage = 0;
            $gratuitySetting->eligibility_years= $request->eligibility_years;
        }

        $gratuitySetting->save();

        session()->flash('success', 'Gratuity setting created successfully.');
        return redirect()->back();

    }

    /**
     * Show edit form.
     */
    public function edit(GratuitySetting $gratuity_setting)
    {
        return response()->json($gratuity_setting);
    }

    /**
     * Update gratuity setting.
     */
    public function update(Request $request, GratuitySetting $gratuity_setting)
    {
        $request->validate([
            'id' => 'required|integer',
            'title' => 'required|string|max:255',
            'employee_contribution_percentage' => 'required|numeric|min:0',
             'company_contribution_percentage' => 'required|numeric|min:0',
            'eligibility_years' => 'required|integer|min:0',
        ]);

        $gratuity_setting->update([
            'title'                 => $request->title,
            'employee_contribution_percentage'            => $request->employee_contribution_percentage,
            'company_contribution_percentage'            => $request->company_contribution_percentage,
            'eligibility_years'  => $request->eligibility_years,
            'description'           => $request->description,
        ]);
//        $setting = EmployeeGratuitySetting::findOrFail($request->id);
//        $setting->update($request->only([
//            'title',
//            'type',
//            'percentage',
//            'min_years_of_service',
//            'description'
//        ]));

        session()->flash('success', 'Gratuity setting updated successfully.');
        return redirect()->back();
    }

    /**
     * Delete a gratuity setting.
     */
    public function destroy($id)
    {
        GratuitySetting::findOrFail($id)->delete();
        return redirect()->route('gratuity-settings.index')
            ->with('success', 'Gratuity setting deleted successfully.');
    }


    public function assign_roles_index($id)
    {

        $gratuity_setting = GratuitySetting::find($id);
        if($gratuity_setting) {
            $roles =  Role::where('guard_name','employee')->where('status', 1)->get();
            $assignedRoleIds = RoleGratuitySetting::where('gratuity_setting_id', $id)
                ->pluck('role_id')
                ->toArray();
            return view('admin.employee_settings.gratuity_assign_role')->with(['roles'=>$roles,'id'=>$id,'gratuity_setting'=>$gratuity_setting,'assignedRoleIds'=>$assignedRoleIds]);
        }
        return view('admin.employee_settings.gratuities')->with([['errors','Role not found']]);
    }

    public function assign_roles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'integer|exists:hr_roles,id',
        ]);

        try {

            DB::beginTransaction();

            $gratuity_setting = GratuitySetting::where('id',$id)->where('status',1)->first();
            if($gratuity_setting)
            {
                // delete any existing mapping of this role
                RoleGratuitySetting::whereIn('role_id',$request->roles)->delete();

                // delete any existing mapping of this gratuity
                RoleGratuitySetting::where('gratuity_setting_id', $id)->delete();

                $insertData = [];
                foreach ($request->roles as $roleId) {

                    $insertData[] = [
                        'role_id' => $roleId,
                        'gratuity_setting_id' => $id,
                        'created_by' => auth('admin')->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($insertData)) {
                    RoleGratuitySetting::insert($insertData);
                }

                DB::commit();
                return redirect()
                    ->route('admin.gratuity_settings.index')
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

    public function assign_roles_old(Request $request)
    {


//        $request->validate([
//            'role_id' => 'required|array|min:1',
//            'ids' => 'required|array|min:1',
//        ]);
//
//        DB::beginTransaction();
//        try {
//
//            // Ek hi query me delete sab role_ids ke liye
//            RoleGratuitySetting::whereIn('role_id', $request->role_id)->delete();
//
//            $insertData = [];
//            foreach ($request->role_id as $roleId) {
//                foreach ($request->ids as $gratuityId) {
//                    $insertData[] = [
//                        'role_id' => $roleId,
//                        'gratuity_setting_id' => $gratuityId,
//                        'created_by' => auth()->id(),
//                        'updated_by' => auth()->id(),
//                        'created_at' => now(),
//                        'updated_at' => now(),
//                    ];
//                }
//            }
//
//            // Bulk insert
//            if (!empty($insertData)) {
//                RoleGratuitySetting::insert($insertData);
//                DB::commit();
//            }else {
//                DB::rollBack(); // Delete undo ho jaye
//                return redirect()->back()->with('error', 'No gratuity settings selected.');
//            }
//
//
//            return redirect()->back()->with('success', 'Gratuity settings assigned successfully.');
//
//        } catch (\Throwable $th) {
//            DB::rollBack();
//            Log::channel('admin_log')->error([
//                'message' => $th->getMessage(),
//                'file'    => $th->getFile(),
//                'line'    => $th->getLine(),
//                'trace'   => $th->getTraceAsString(),
//            ]);
//            return redirect()->back()->with('error', 'Something went wrong.');

        }


}
