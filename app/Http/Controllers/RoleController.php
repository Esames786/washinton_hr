<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
//use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Role::select(['id', 'name', 'guard_name', 'status']);

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $action = '<div class="d-flex justify-content-center gap-2">
                                 <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn">
                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                </button></div>';
                    return $action;
                })
                ->addColumn('access', function ($row) {
                    return ' <a href='.route('admin.permissions.index',['id'=> $row->id]).' class="btn btn-outline-primary-600 radius-8 px-20 py-11">Add Permission</a>';

//                    if($row->guard_name == 'admin'){
//                        return ' <a href='.route('admin.permissions.index',['id'=> $row->id]).' class="btn btn-outline-primary-600 radius-8 px-20 py-11">Add Permission</a>';
//                    }
//                    return '-';
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
                ->rawColumns(['status', 'action','access'])
                ->make(true);
        }

        return view('admin.user_management.roles');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'user_type' => 'required|string|in:admin,employee',
//            'guard_bit'  => 'required|in:1,2',
        ]);

//        $guardName = $request->guard_bit == 1 ? 'admin' : 'employee';

        Role::create([
            'name'       => $request->name,
            'guard_name' => $request->user_type,
            // 'status'     => $request->status, // commented as per request
        ]);

        return redirect()->back()->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        return response()->json($role);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
//            'user_type' => 'required|string|in:admin,employee',
        ]);

//        $guardName = $request->guard_bit == 1 ? 'admin' : 'employee';

        $role->update([
            'name'       => $request->name,
//            'guard_name' => $request->user_type,
            // 'status'     => $request->status, // commented as per request
        ]);

        return redirect()->back()->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        return response()->json(['success' => 'Role deleted successfully.']);
    }
}
