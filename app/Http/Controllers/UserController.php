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
        if($request->status == 'suspend'){
            $user->block_until = \Carbon\Carbon::parse(now())->addHours($request->time);
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
        //
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
        User::where('status', 'suspend')->where('block_until', '<', now())->update([
            'status' => 'fine',
            'block_until' => null
        ]);
    
    }
}
