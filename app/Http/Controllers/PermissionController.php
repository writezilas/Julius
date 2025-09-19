<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function denied()
    {
        return view('error.permission-denied');
    }

    public function update()
    {
        
        $jsonPath = database_path('seeders/data/permissions.json');
        
        // Decode to array
        $permissions = collect(json_decode(file_get_contents($jsonPath), true));
        

        foreach ($permissions as $permission) {
            
            DB::table('permissions')->updateOrInsert([
                'name' => $permission['name']
            ], $permission);
        }

        DB::table('role_has_permissions')->truncate();
        $role = Role::find(1);

        $allCurrentPermissions = Permission::all();

        foreach ($allCurrentPermissions as $currentPermission) {
            $permission = Permission::firstOrCreate(['name' => $currentPermission->name]);
            if(!$role->hasPermissionTo($currentPermission->name)){
                $role->givePermissionTo($permission);
            }
        }
        return 'done';
    }
}
