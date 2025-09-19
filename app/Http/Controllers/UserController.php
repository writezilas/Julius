<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
     * Unified user management interface
     */
    public function unifiedIndex(Request $request)
    {
        $query = User::where('role_id', 2);
        
        // Apply status filter
        if ($request->has('status') && !empty($request->status)) {
            $status = $request->status;
            $query->where('status', $status);
        }
        
        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('username', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }
        
        // Apply date filters
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Get paginated results
        $users = $query->latest()->paginate(25)->appends($request->query());
        
        // Get user statistics
        $stats = $this->getUserStats();
        
        return view('admin-panel.users.unified', compact('users', 'stats'));
    }
    
    /**
     * Get user statistics for dashboard cards
     */
    private function getUserStats()
    {
        return [
            'total' => User::where('role_id', 2)->count(),
            'active' => User::where('role_id', 2)->where('status', 'active')->count(),
            'suspended' => User::where('role_id', 2)->where('status', 'suspended')->count(),
            'blocked' => User::where('role_id', 2)->where('status', 'blocked')->count(),
        ];
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
        if($request->status == 'suspend' || $request->status == 'suspended'){
            // Standardize to 'suspended' status
            $user->status = 'suspended';
            
            if($request->has('suspension_duration') && $request->suspension_duration > 0) {
                $user->suspension_until = \Carbon\Carbon::now()->addHours($request->suspension_duration);
            } else {
                // Default to 24 hours if no duration specified
                $user->suspension_until = \Carbon\Carbon::now()->addHours(24);
            }
            
            // Set suspension reason to 'manual' for admin suspensions
            $user->suspension_reason = 'manual';
            
        } elseif($request->status == 'block' || $request->status == 'blocked') {
            // Standardize to 'blocked' status
            $user->status = 'blocked';
            
            // Admin blocks are permanent - do not set block_until
            // Temporary blocks are handled by the system, not admin interface
            $user->block_until = null;
            
        } else {
            // Clear suspension and blocking when status changes to something else (e.g., active)
            $user->suspension_until = null;
            $user->suspension_reason = null;
            $user->block_until = null;
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
        User::whereIn('status', ['suspend', 'suspended'])
            ->where('suspension_until', '<', now())
            ->update([
                'status' => 'active',
                'suspension_until' => null,
                'suspension_reason' => null
            ]);
        
        // Also handle old block_until logic for backward compatibility
        User::whereIn('status', ['suspend', 'suspended'])
            ->where('block_until', '<', now())
            ->update([
                'status' => 'active',
                'block_until' => null,
                'suspension_reason' => null
            ]);
    }
}
