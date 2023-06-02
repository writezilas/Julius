<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pageTitle = 'Role list';
        $roles = Role::paginate(25);
        return view('admin-panel.roles.index', compact('roles', 'pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'bail|required|unique:roles|max:255',
        ]);

        $data['guard_name'] = 'auth';

        if(Role::create($data)) {
            toastr()->success('Role has been created successfully');
        }

        return redirect()->back();

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $data = $request->validate([
            'name' => 'bail|required|unique:roles|max:255',
        ]);

        if($role->update($data)) {
            toastr()->success('Role has been updated successfully');
        }else {
            toastr()->error('Failed to updated role');
        }

        return redirect()->back();
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        if($role->delete()) {
            toastr()->success('Role has been deleted successfully');
        }else {
            toastr()->error('Failed to delete role');
        }

        return back();
    }

    public function permission($role_id) {
        $pageTitle = 'Permission edit of role';
         $role = Role::find($role_id);
        $permissionsOfTheRoles = Role::findByName($role->name)->permissions;
        $all_modules = Permission::groupBy('module_name')->get('module_name');

        $permissions = [];
        foreach ($all_modules as $module) {
            $moduleWisePermissions = Permission::where('module_name', $module->module_name)->get();
            $permissionsPerModule = [
                'module_name' => $module->module_name,
                'permission' => $moduleWisePermissions
            ];
            $permissions[] = $permissionsPerModule;
        }

        $allPermission = [];
        foreach ($permissionsOfTheRoles as $permission)
            $allPermission[] = $permission->name;
        if(empty($allPermission))
            $allPermission[] = 'no-permission';


        return view('admin-panel.roles.permission', compact('pageTitle', 'role', 'allPermission', 'permissions'));
    }


    public function updatePermission(Request $request, $id)
    {
        $role = \Spatie\Permission\Models\Role::find($id);

        $allCurrentPermissions = Permission::all();

        foreach ($allCurrentPermissions as $currentPermission) {
            if($request->has($currentPermission->name)){
                $permission = Permission::firstOrCreate(['name' => $currentPermission->name]);
                if(!$role->hasPermissionTo($currentPermission->name)){
                    $role->givePermissionTo($permission);
                }
            }
            else {
                $role->revokePermissionTo($currentPermission->name);
            }
        }


        toastr()->success('Permissions has been assigned successfully');
        return back();
    }
}
