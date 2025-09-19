<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\EmailAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class AnnouncementController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pageTitle = 'Announcements';
        $announcements = Announcement::orderBy('id', 'desc')->get();
        return view('admin-panel.communications.announcement.index', compact('pageTitle', 'announcements'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createAnnouncement()
    {
        $pageTitle = 'Create announcement';
        return view('admin-panel.communications.announcement.create', compact('pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createEmail()
    {
        $pageTitle = 'Create email';
        $users = User::where('role_id', 2)->get();
        return view('admin-panel.communications.email', compact('pageTitle', 'users'));
    }

    public function sendEmail(Request $request) {

        $request->validate([
            'title' => 'required',
            'users' => 'required',
            'content' => 'required',
        ]);

        foreach ($request->users as $user) {
            $user = User::find($user);
            $data = [
                'title' => $request->title,
                'content' => $request->content,
                'username' => $user->username,
            ];
            try {
                Notification::send($user, new EmailAnnouncement($data));
            } catch (\Exception $th) {
                // log
                \Log::error('File:' . $th->getFile() . 'Line:' . $th->getLine() . 'Message:' . $th->getMessage());
            }
            
        }
        toastr()->success('Email has been sent successfully');

        return back();
    }
    
    public function createSms()
    {
        $pageTitle = 'Send sms';
        $users = User::where('role_id', 2)->get();
        return view('admin-panel.communications.sms', compact('pageTitle', 'users'));
    }

    public function sendSms(Request $request) {

        $request->validate([
            'users'   => 'required',
            'content' => 'required',
        ]);

        $sms = [];
        $numbers = [];
        foreach ($request->users as $user) {
            $user = User::find($user);
            
            $phone = $user->phone;
            if (substr($phone, 0, 1) == '0') {
                $phone = '254' . substr($phone, 1);
            }
            $numbers[] = $phone;
        }
        $sms = [ 
            [
                'mobile' => $numbers,
                'msg'    => $request->content,
            ],
            [
                'mobile' => $numbers,
                'msg'    => $request->content,
            ]
        ];
        
        send_sms($sms);

        toastr()->success('SMS has been sent successfully');

        return back();
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
            'title' => 'required',
            'excerpt' => 'required',
            'description' => 'required',
            'video_url' => 'nullable',
        ]);

        if ($request->file('image')) {
            $data['image'] = $request->image->store('uploads/announcement', 'public');
        }

        if(Announcement::create($data)) {
            toastr()->success('Announcement has been created successfully');
        }else {
            toastr()->error('Failed to create announcement');
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
        $announcement = Announcement::findOrFail($id);
        $pageTitle = 'Edit announcement';

        return view('admin-panel.communications.announcement.edit', compact('pageTitle', 'announcement'));
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
        $announcement = Announcement::findOrFail($id);
        $data = $request->validate([
            'title' => 'required',
            'excerpt' => 'required',
            'description' => 'required',
            'video_url' => 'nullable'
        ]);

        if ($request->file('image')) {
            $data['image'] = $request->image->store('uploads/announcement', 'public');
        }

        if($announcement->update($data)) {
            toastr()->success('Announcement has been updated successfully');
        }else {
            toastr()->error('Failed to update announcement');
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
        $announcement = Announcement::findOrFail($id);
        if($announcement->delete()) {
            toastr()->success('Announcement has been deleted successfully');
        }else {
            toastr()->error('Failed to delete announcement');
        }

        return back();
    }
}
