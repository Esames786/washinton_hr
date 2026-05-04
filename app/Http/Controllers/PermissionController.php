<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
//use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class PermissionController extends Controller
{
    public function index($id)
    {

        $role = Role::find($id);
        if($role) {
            $permissions = Permission::where('guard_name',$role->guard_name)->get()->map(function ($permission) {
                $parts = explode('.', $permission->name);
                $permission->module = $parts[1] ?? ''; // e.g. 'permissions'
                $permission->action = end($parts);     // e.g. 'index'
                return $permission;
            });
            return view('admin.user_management.permissions')->with(['permissions'=>$permissions,'id'=>$id,'guard_name'=>$role->guard_name]);
        }
        return view('admin.user_management.roles')->with([['errors','Role not found']]);

    }

    public function store(Request $request)
    {
        $guardName = $request->get('guard_name');

        $excludedRoutes = [
            // Admin auth routes
            'admin.login',
            'admin.admin_login',
            'admin.logout',
            'admin.not_found',
            'admin.dashboard',

            // Admin roles routes
            'admin.roles.index',
            'admin.roles.create',
            'admin.roles.store',
            'admin.roles.show',
            'admin.roles.edit',
            'admin.roles.update',
            'admin.roles.destroy',

            // Admin permissions routes
            'admin.permissions.index',
            'admin.permissions.store',
            'admin.permissions.assignPermission',
        ];

        $routeNames = collect(Route::getRoutes())
            ->map->getName()
            ->filter(function ($name) use ($guardName, $excludedRoutes) {
                return $name &&
                    str_starts_with($name, $guardName . '.') && // only this guard's routes
                    !in_array($name, $excludedRoutes);
            })
            ->unique()
            ->values()
            ->toArray();

        $existing = Permission::where('guard_name', $guardName)->pluck('name')->toArray();

        // Calculate differences
        $newRoutes = array_diff($routeNames, $existing);
        $oldRoutes = array_diff($existing, $routeNames);


        // Delete removed permissions
        if ($oldRoutes) {
            Permission::whereIn('name', $oldRoutes)
                ->where('guard_name', $guardName)
                ->delete();
        }

        foreach ($newRoutes as $route) {
            Permission::create([
                'name' => $route,
                'guard_name' => $guardName,
            ]);
        }

        session()->flash('success', 'Permissions synced for guard:'. $guardName);
        return redirect()->back();
    }

//    public function store(Request $request)
//    {
//        $routes = Route::getRoutes();
//
//        $filteredRoutes = collect($routes)->filter(function ($route) {
//            return in_array('admin.check', $route->middleware());
//        });
//
//        $routeNames = [];
//
//        foreach ($filteredRoutes as $route) {
//            $excludedRoutes = [
//                'admin.roles.index',
//                'admin.roles.create',
//                'admin.roles.store',
//                'admin.roles.show',
//                'admin.roles.edit',
//                'admin.roles.update',
//                'admin.roles.destroy',
//                'admin.permissions.index',
//                'admin.permissions.store',
//                'admin.permissions.assignPermission',
//            ];
//
//            if ($route->getName() !== null && !in_array($route->getName(), $excludedRoutes)) {
//                $routeNames[] = $route->getName();
//            }
//        }
//
//        $existingPermission = Permission::where('guard_name',$request->guard_name)->pluck('name')->toArray();
//        $newRoutes = array_diff($routeNames, $existingPermission);
//        $oldRoutes = array_diff($existingPermission, $routeNames);
//
//        if(count($oldRoutes) > 0){
//            foreach ($oldRoutes as $oldRoute) {
//                Permission::where('name', 'like', '%' . trim($oldRoute) . '%')->delete();
//            }
//        }
//
//        if (count($newRoutes) > 0) {
//            foreach ($newRoutes as $newRoute) {
//                Permission::create(['name' => $newRoute, 'guard_name' => $request->guard_name]);
//            }
//        }
//        session()->flash('success', 'Permissions Sync');
//        return redirect()->back();
//
//    }

    public function assignPermission(Request $request, $id)
    {

        $role = Role::find($id);
        $role->syncPermissions($request->permissions);
        return view('admin.user_management.roles')->with(['success'=>'Permissions Assigned To Role']);
    }


}
