<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    protected $pageTitle;
    protected $emptyMessage;

    protected function filterUsers($type)
    {

        $users = User::query();
        $this->pageTitle    = ucfirst($type) . ' Users';
        $this->emptyMessage = 'No ' . $type . ' users found';

        if ($type != 'all') {
            $users = $users->$type();
        }

        return $users->where('role_id', 2)->latest()->get();
    }

    public function index()
    {
        $segments       = request()->segments();
        $users       = $this->filterUsers(end($segments));
        $pageTitle      = $this->pageTitle;
        $emptyMessage   = $this->emptyMessage;

        return view('admin-panel.users.index', compact('pageTitle', 'emptyMessage', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $pageTitle = "User view";
        $emptyMessage = "No data found";

        return view('admin-panel.users.view', compact('pageTitle', 'emptyMessage', 'user'));
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //    public function statusUpdate(Request $request,$id)
    //    {
    //        $update = User::where('id', $id)->update(['status' => $request->status]);
    //        return 'success';
    //    }

    public function statusUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->status = $request->status;
        
        // Handle suspension with duration
        if($request->status == 'suspend'){
            if($request->has('suspension_duration') && $request->suspension_duration > 0) {
                $user->suspension_until = \Carbon\Carbon::now()->addHours($request->suspension_duration);
            } else {
                // Default to 24 hours if no duration specified
                $user->suspension_until = \Carbon\Carbon::now()->addHours(24);
            }
        } else {
            // Clear suspension when status changes to something else
            $user->suspension_until = null;
        }
        
        // Handle blocking with duration (keep existing functionality)
        if($request->status == 'block' && $request->has('time')){
            $user->block_until = \Carbon\Carbon::now()->addHours($request->time);
        }
        
        $user->save();
        toastr()->success('User status updated successfully');
        return back();
    }

    public function changeMode(){
        $user = User::findOrFail(auth()->user()->id);
        $user->mode = $user->mode == 'dark' ? 'light' : 'dark';
        $user->save();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:255|unique:users,phone,' . $user->id,
            'refferal_code' => 'nullable|string|max:255',
            'balance' => 'nullable|numeric|min:0',
            'mpesa_name' => 'nullable|string|max:255',
            'mpesa_no' => 'nullable|string|max:255',
            'mpesa_till_no' => 'nullable|string|max:255',
            'mpesa_till_name' => 'nullable|string|max:255',
        ]);
        
        try {
            // Update basic user information
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'refferal_code' => $request->refferal_code,
                'balance' => $request->balance ?? $user->balance,
            ]);
            
            // Update business profile (Mpesa information)
            $existingBusinessProfile = json_decode($user->business_profile, true) ?? [];
            $updatedBusinessProfile = array_merge($existingBusinessProfile, [
                'mpesa_name' => $request->mpesa_name ?? $existingBusinessProfile['mpesa_name'] ?? '',
                'mpesa_no' => $request->mpesa_no ?? $existingBusinessProfile['mpesa_no'] ?? '',
                'mpesa_till_no' => $request->mpesa_till_no ?? $existingBusinessProfile['mpesa_till_no'] ?? '',
                'mpesa_till_name' => $request->mpesa_till_name ?? $existingBusinessProfile['mpesa_till_name'] ?? '',
            ]);
            
            $user->update([
                'business_profile' => json_encode($updatedBusinessProfile)
            ]);
            
            // Log the changes for security purposes
            $log = new \App\Models\Log();
            $log->remarks = "User information updated by admin: " . auth()->user()->name;
            $log->type = "admin_update";
            $log->value = 0;
            $log->user_id = $user->id;
            $user->logs()->save($log);
            
            toastr()->success('User information updated successfully!');
            return redirect()->back();
            
        } catch (\Exception $e) {
            toastr()->error('Error updating user information: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function revertSuspendUsers()
    {
        // Revert suspended users whose suspension time has expired
        User::where('status', 'suspend')
            ->where('suspension_until', '<', now())
            ->update([
                'status' => 'fine',
                'suspension_until' => null
            ]);
        
        // Also handle old block_until logic for backward compatibility
        User::where('status', 'suspend')
            ->where('block_until', '<', now())
            ->update([
                'status' => 'fine',
                'block_until' => null
            ]);
    }
}
