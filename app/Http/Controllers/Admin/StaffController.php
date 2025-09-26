<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pageTitle = 'Staffs';
        $users = User::where('role_id', '!=', 2)->get();

        return view('admin-panel.staffs.index', compact('pageTitle', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTitle = 'Create Staff';
        $roles = Role::where('id', '!=', 2)->get();

         return view('admin-panel.staffs.create', compact('roles', 'pageTitle'));
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
            'name' => 'bail|required',
            'username' => 'bail|required|unique:users',
            'email' => 'bail|required|unique:users',
            'phone' => 'bail|required|unique:users',
            'role_id' => 'bail|required|numeric',
            'password' => 'bail|required|min:6',
        ],
        [
            'role_id.required' => 'Role field is required',
        ]);

        $data['password'] = Hash::make($request->password);
        $data['email_verified_at'] = date('Y-m-d H:i:s');
        $data['created_at'] = now();
        $data['avatar'] = 'assets/images/users/avatar-1.jpg';

        $user = User::create($data);

        if($user) {
            $role = Role::find($request->role_id);
            $user->assignRole($role->name);
            toastr()->success('Staff created successfully');
        }else {
            toastr()->error('Failed to create staff');
        }

        return back();


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pageTitle = 'Edit Staff';
        $roles = Role::where('id', '!=', 2)->get();

        $user = User::findOrFail($id);

        return view('admin-panel.staffs.edit', compact('user','roles', 'pageTitle'));
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
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'bail|required',
            'username' => 'bail|required|unique:users,username,'.$id,
            'email' => 'bail|required|unique:users,email,'.$id,
            'phone' => 'bail|required|unique:users,phone,'.$id,
            'role_id' => 'bail|required|numeric',
        ],
            [
                'role_id.required' => 'Role field is required',
            ]);

        if($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $updatedUser = $user->update($data);

        if($updatedUser) {
            $role = Role::find($request->role_id);
            $user->syncRoles($role->name);
            toastr()->success('Staff has been update successfully');
        }else {
            toastr()->error('Failed to update staff');
        }

        return back();

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if($user->delete()) {
            toastr()->success('Staff has been deleted successfully');
        }else {
            toastr()->error('Failed to delete staff');
        }

        return back();
    }
}
