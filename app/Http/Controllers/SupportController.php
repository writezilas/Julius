<?php

namespace App\Http\Controllers;

use App\Models\Support;
use App\Notifications\NewTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class SupportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $this->validate($request, [
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'required',
            'telephone'  => 'required',
            'message'    => 'required',
        ]);

        $support = Support::create([
            'user_id'    => auth()->user()->id,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'number'     => $request->telephone,
            'username'   => $request->username,
            'message'    => $request->message,
        ]);

        if($support) {
            $support_email = get_gs_value('support_email') ?? 'support@autobidder.live'; 
            Notification::route('mail', $support_email)->notify(new NewTicket($support));
            toastr()->success('Support request submitted successfully. We will reach you as soon as possible');
        }else {
            toastr()->error('Failed to submit support request');
        }

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function show(Support $support)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function edit(Support $support)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Support $support)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Support  $support
     * @return \Illuminate\Http\Response
     */
    public function destroy(Support $support)
    {
        //
    }


    public function supportsForAdmin() {
        $supports = Support::orderBy('id', 'desc')->get();
        $pageTitle = 'Supports';
        $supportFormEnabled = get_gs_value('support_form_enabled') ?? 1;

        return view('admin-panel.communications.supports', compact('pageTitle', 'supports', 'supportFormEnabled'));

    }

    public function toggleSupportForm(Request $request)
    {
        $enabled = $request->input('enabled', 0);
        
        \App\Models\GeneralSetting::updateOrCreate(
            ['key' => 'support_form_enabled'],
            ['value' => $enabled]
        );
        
        $status = $enabled ? 'enabled' : 'disabled';
        
        return response()->json([
            'success' => true,
            'message' => "Support form has been {$status} successfully",
            'status' => $status
        ]);
    }


}
