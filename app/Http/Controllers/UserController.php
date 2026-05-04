<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Admin::select('id', 'name', 'email', 'status', 'role_id')->where('role_id','!=', 1);

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex justify-content-center gap-2">
                                <button type="button" class="bg-success-focus text-success-600 bg-hover-success-200 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle edit_btn" data-id="'.$row->id.'">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </button>
                            </div>';
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<button class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm active_btn" data-id="'.$row->id.'">Active</button>'
                        : '<button class="bg-neutral-200 text-neutral-600 border border-neutral-400 px-24 py-4 radius-4 fw-medium text-sm inactive_btn" data-id="'.$row->id.'">Inactive</button>';
                })
                ->filter(function ($query) {
                    if (request()->has('search') && request('search')['value'] != '') {
                        $search = request('search')['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                    }
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $roles = Role::where('id', '!=', 1)->where('guard_name','admin')->where('status',1)->get();
        return view('admin.user_management.users')->with('roles', $roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email',
            'password' => 'required|min:8',
            'role_id'  => 'required|integer|exists:hr_roles,id',
//            'status'   => 'required|in:0,1',
            'profile_path' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $admin = new Admin();
        $admin->name     = $request->name;
        $admin->email    = $request->email;
        $admin->password = Hash::make($request->password);
        $admin->role_id = $request->role_id;
//        $admin->status   = $request->status;
//        $admin->created_by = auth()->id();
        $admin->save();
        // Profile picture upload
        if ($request->hasFile('profile_path')) {
            $file = $request->file('profile_path');
            $filename = 'profile_' . $admin->id . '.' . $file->extension();
            $file->move(public_path('Uploads/admins/' . $admin->id . '/'), $filename);
            $admin->profile_path = 'Uploads/admins/' . $admin->id . '/' . $filename;

        }

        $admin->save();
        session()->flash('success', 'User created successfully.');
        return redirect()->back();
    }

    public function edit(Admin $user)
    {
        // Profile path ko full URL me convert karo
        $user->profile_path = $user->profile_path ? asset($user->profile_path) : null;
        return response()->json($user);
    }

    public function update(Request $request, Admin $user)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'   => 'required|email|unique:admins,email,' . $user->id,
            'role_id'  => 'required|integer|exists:hr_roles,id',
//            'password' => 'nullable|min:6',
//            'status'   => 'required|in:0,1',
            'profile_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user->name   = $request->name;
        $user->email  = $request->email;
        $user->role_id = $request->role_id;

        $role = Role::find($request->role_id);
        if($role){
            $user->syncRoles([$role]);
        }

//        if ($request->filled('password')) {
//            $user->password = Hash::make($request->password);
//        }
//        $user->status = $request->status;
//        $user->updated_by = auth()->id();

        if ($request->hasFile('profile_path')) {
            if ($user->profile_path && file_exists(public_path($user->profile_path))) {
                unlink(public_path($user->profile_path));
            }
            $file = $request->file('profile_path');
            $filename = 'profile_' . $user->id . '.' . $file->extension();
            $file->move(public_path('Uploads/admins/' . $user->id . '/'), $filename);
            $user->profile_path = 'Uploads/admins/' . $user->id . '/' . $filename;
        }

        $user->save();

        session()->flash('success', 'User updated successfully.');
        return redirect()->back();
    }

    public function destroy(Admin $user)
    {
        $user->delete();
        return response()->json(['success' => 'User deleted successfully.']);
    }
}
