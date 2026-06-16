<?php

namespace App\Http\Controllers\EmployeeSettings;

use App\Http\Controllers\Controller;
use App\Models\CommissionSlab;
use App\Models\CommissionSetting;
use App\Models\CommissionTargetType;
use App\Models\CommissionType;
use App\Models\Role;
use App\Models\RoleCommissionSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class EmployeeCommissionSettingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CommissionSetting::with('commission_type', 'target_type')
                ->select(
                    'id',
                    'title',
                    'description',
                    'value',
                    'is_slab_based',
                    'status',
                    'commission_type_id',
                    'target_type_id',
                );

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex justify-content-center gap-2">
                                <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn" data-id="'.$row->id.'">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </button>
                            </div>';
                })
                ->addColumn('commission_type', fn($row) => $row->commission_type?->name ?? '')
                ->addColumn('target_type',     fn($row) => $row->target_type?->name ?? '')
                ->addColumn('value_display', function ($row) {
                    if ($row->is_slab_based) {
                        return '<span class="badge bg-info-600 text-white px-12 py-4 radius-4 fw-medium text-sm">Slab-Based</span>';
                    }
                    return $row->value;
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<button class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm">Active</button>'
                        : '<button class="bg-neutral-200 text-neutral-600 border border-neutral-400 px-24 py-4 radius-4 fw-medium text-sm">Inactive</button>';
                })
                ->filter(function ($query) {
                    if (request()->has('search') && request('search')['value'] != '') {
                        $query->where('title', 'like', '%'.request('search')['value'].'%');
                    }
                })
                ->rawColumns(['status', 'action', 'value_display'])
                ->make(true);
        }

        $commissionTypes = CommissionType::all();
        $targetTypes     = CommissionTargetType::all();

        return view('admin.employee_settings.commissions')->with(compact('commissionTypes', 'targetTypes'));
    }

    public function store(Request $request)
    {
        $isSlabBased = $request->boolean('is_slab_based');

        $rules = [
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'commission_type_id' => 'required|integer',
            'target_type_id'     => 'nullable|integer',
            'status'             => 'nullable|in:0,1',
        ];

        if ($isSlabBased) {
            $rules['slabs']               = 'required|array|min:1';
            $rules['slabs.*.profit_from'] = 'required|numeric|min:0';
            $rules['slabs.*.profit_to']   = 'nullable|numeric|min:0';
            $rules['slabs.*.value']       = 'required|numeric|min:0';
        } else {
            $rules['value'] = 'required|numeric';
        }

        $request->validate($rules);

        $commission_setting                    = new CommissionSetting();
        $commission_setting->title             = $request->title;
        $commission_setting->description       = $request->description;
        $commission_setting->commission_type_id = $request->commission_type_id;
        $commission_setting->value             = $isSlabBased ? 0 : $request->value;
        $commission_setting->is_slab_based     = $isSlabBased ? 1 : 0;
        $commission_setting->target_type_id    = $request->target_type_id ?: null;
        $commission_setting->status            = $request->input('status', 1);
        $commission_setting->save();

        if ($isSlabBased && $request->slabs) {
            foreach ($request->slabs as $slab) {
                CommissionSlab::create([
                    'commission_setting_id' => $commission_setting->id,
                    'profit_from'           => $slab['profit_from'],
                    'profit_to'             => isset($slab['profit_to']) && $slab['profit_to'] !== '' ? $slab['profit_to'] : null,
                    'value'                 => $slab['value'],
                ]);
            }
        }

        session()->flash('success', 'Commission setting added successfully.');
        return redirect()->back();
    }

    public function edit(CommissionSetting $commission_setting)
    {
        $commission_setting->load('commission_type', 'target_type', 'slabs');
        return response()->json($commission_setting);
    }

    public function update(Request $request, CommissionSetting $commission_setting)
    {
        $isSlabBased = $request->boolean('is_slab_based');

        $rules = [
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'commission_type_id' => 'required|integer',
            'target_type_id'     => 'nullable|integer',
            'status'             => 'nullable|in:0,1',
        ];

        if ($isSlabBased) {
            $rules['slabs']               = 'required|array|min:1';
            $rules['slabs.*.profit_from'] = 'required|numeric|min:0';
            $rules['slabs.*.profit_to']   = 'nullable|numeric|min:0';
            $rules['slabs.*.value']       = 'required|numeric|min:0';
        } else {
            $rules['value'] = 'required|numeric';
        }

        $request->validate($rules);

        $commission_setting->title              = $request->title;
        $commission_setting->description        = $request->description;
        $commission_setting->commission_type_id = $request->commission_type_id;
        $commission_setting->value              = $isSlabBased ? 0 : $request->value;
        $commission_setting->is_slab_based      = $isSlabBased ? 1 : 0;
        $commission_setting->target_type_id     = $request->target_type_id ?: null;
        $commission_setting->status             = $request->input('status', 1);
        $commission_setting->save();

        // Replace slabs on every update (cascade delete handles old ones)
        $commission_setting->slabs()->delete();

        if ($isSlabBased && $request->slabs) {
            foreach ($request->slabs as $slab) {
                CommissionSlab::create([
                    'commission_setting_id' => $commission_setting->id,
                    'profit_from'           => $slab['profit_from'],
                    'profit_to'             => isset($slab['profit_to']) && $slab['profit_to'] !== '' ? $slab['profit_to'] : null,
                    'value'                 => $slab['value'],
                ]);
            }
        }

        session()->flash('success', 'Commission setting updated successfully.');
        return redirect()->back();
    }

    public function destroy($id)
    {
        CommissionSetting::findOrFail($id)->delete();
        return response()->json(['success' => 'Record deleted successfully.']);
    }

    public function assign_roles_index($id)
    {
        $commission_setting = CommissionSetting::find($id);
        if ($commission_setting) {
            $roles           = Role::where('guard_name', 'employee')->where('status', 1)->get();
            $assignedRoleIds = RoleCommissionSetting::where('commission_setting_id', $id)->pluck('role_id')->toArray();
            return view('admin.employee_settings.commission_assign_role')
                ->with(['roles' => $roles, 'id' => $id, 'commission_setting' => $commission_setting, 'assignedRoleIds' => $assignedRoleIds]);
        }
        return view('admin.employee_settings.commissions')->with([['errors', 'Role not found']]);
    }

    public function assign_roles(Request $request, $id)
    {
        $request->validate([
            'roles'   => 'required|array|min:1',
            'roles.*' => 'integer|exists:hr_roles,id',
        ]);

        try {
            DB::beginTransaction();

            $commission_setting = CommissionSetting::where('id', $id)->where('status', 1)->first();
            if ($commission_setting) {
                RoleCommissionSetting::whereIn('role_id', $request->roles)->delete();
                RoleCommissionSetting::where('commission_setting_id', $id)->delete();

                $insertData = [];
                foreach ($request->roles as $roleId) {
                    $insertData[] = [
                        'role_id'               => $roleId,
                        'commission_setting_id' => $id,
                        'created_by'            => auth('admin')->id(),
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                }

                if (!empty($insertData)) {
                    RoleCommissionSetting::insert($insertData);
                }

                DB::commit();
                return redirect()->route('admin.commission_settings.index')->with('success', 'Roles assigned successfully.');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('admin_log')->error([
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Something went wrong while assigning roles.');
        }
    }
}
