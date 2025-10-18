<?php

namespace App\Http\Controllers;

use App\Models\Support;
use App\Models\AdminNotification;
use App\Notifications\NewTicket;
use App\Notifications\AdminSupportReply;
use App\Services\SmtpErrorHandlingService;
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
            // PRIORITY 1: Create admin notification FIRST - This must NEVER fail
            // This ensures admins are always notified regardless of email issues
            try {
                $notification = AdminNotification::newSupportRequest($support, auth()->user());
                \Log::info('PRIORITY 1 COMPLETED: Admin notification created successfully', [
                    'support_id' => $support->id,
                    'notification_id' => $notification->id,
                    'user' => auth()->user()->username,
                    'priority' => 1,
                    'operation' => 'admin_notification_created'
                ]);
            } catch (\Exception $e) {
                // This should almost never happen, but if it does, log it as critical
                \Log::critical('PRIORITY 1 FAILED: Admin notification creation failed - This is a critical system error!', [
                    'support_id' => $support->id,
                    'user' => auth()->user()->username,
                    'error' => $e->getMessage(),
                    'priority' => 1,
                    'operation' => 'admin_notification_failed'
                ]);
                // Continue processing even if this fails (extremely rare)
            }
            
            // PRIORITY 2: Attempt email notification (optional) - Can fail safely
            // This is secondary and should never affect the core support process
            try {
                \Log::info('PRIORITY 2 STARTING: Attempting email notification', [
                    'support_id' => $support->id,
                    'user' => auth()->user()->username,
                    'priority' => 2,
                    'operation' => 'email_notification_attempt'
                ]);
                
                $smtpService = new SmtpErrorHandlingService();
                $support_email = get_gs_value('support_email') ?? 'support@autobidder.live';
                
                $emailSent = $smtpService->safelySendNotification(
                    $support_email,
                    new NewTicket($support),
                    'support',
                    ['support_id' => $support->id, 'user' => auth()->user()->username]
                );
                
                if ($emailSent) {
                    \Log::info('PRIORITY 2 COMPLETED: Email notification sent successfully', [
                        'support_id' => $support->id,
                        'user' => auth()->user()->username,
                        'email_sent_to' => $support_email,
                        'priority' => 2,
                        'operation' => 'email_notification_success'
                    ]);
                } else {
                    \Log::warning('PRIORITY 2 SKIPPED: Email notification not sent (SMTP not configured or failed)', [
                        'support_id' => $support->id,
                        'user' => auth()->user()->username,
                        'reason' => 'SMTP not configured or authentication failed',
                        'priority' => 2,
                        'operation' => 'email_notification_skipped'
                    ]);
                }
            } catch (\Exception $e) {
                // Email failure should never affect the support process
                \Log::warning('PRIORITY 2 FAILED: Email notification failed but support process continues', [
                    'support_id' => $support->id,
                    'user' => auth()->user()->username,
                    'error' => $e->getMessage(),
                    'priority' => 2,
                    'operation' => 'email_notification_failed'
                ]);
            }
            
            // FINAL: Always show success message to user
            \Log::info('Support request processing completed successfully', [
                'support_id' => $support->id,
                'user' => auth()->user()->username,
                'admin_notification_attempted' => true,
                'email_notification_attempted' => true,
                'user_notified' => 'success_message_shown'
            ]);
            
            toastr()->success('Support request submitted successfully. We will reach you as soon as possible');
        }else {
            \Log::error('Support request creation failed', [
                'user' => auth()->user()->username,
                'request_data' => $request->except(['password', '_token'])
            ]);
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
    
    /**
     * Send admin reply to support request
     * 
     * @param Request $request
     * @param int $supportId
     * @return \Illuminate\Http\Response
     */
    public function sendAdminReply(Request $request, $supportId)
    {
        $this->validate($request, [
            'admin_reply' => 'required|min:10|max:2000',
        ]);
        
        $support = Support::findOrFail($supportId);
        
        // Update support request with admin reply
        $support->update([
            'admin_reply' => $request->admin_reply,
            'replied_at' => now(),
            'replied_by' => auth()->id(),
            'status' => 1  // Mark as closed after reply
        ]);
        
        // Send email notification to user using SMTP error handling service
        $smtpService = new SmtpErrorHandlingService();
        
        $emailSent = $smtpService->safelySendNotification(
            $support->email,
            new AdminSupportReply($support, auth()->user()),
            'support_reply',
            [
                'support_id' => $support->id,
                'user_email' => $support->email,
                'admin' => auth()->user()->username
            ]
        );
        
        if ($emailSent) {
            toastr()->success('Reply sent successfully to ' . $support->email);
            \Log::info('Admin reply sent successfully', [
                'support_id' => $support->id,
                'user_email' => $support->email,
                'admin' => auth()->user()->username
            ]);
        } else {
            toastr()->success('Reply saved successfully. Email notification could not be sent due to SMTP configuration.');
            \Log::warning('Admin reply saved but email failed', [
                'support_id' => $support->id,
                'user_email' => $support->email,
                'admin' => auth()->user()->username,
                'reason' => 'SMTP not configured or failed'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => $emailSent ? 
                'Reply sent successfully to ' . $support->email : 
                'Reply saved successfully. Email notification could not be sent due to SMTP configuration.',
            'support' => $support->fresh(['user'])
        ]);
    }
    
    /**
     * Get support request details for modal display
     * 
     * @param int $supportId
     * @return \Illuminate\Http\Response
     */
    public function getSupportDetails($supportId)
    {
        $support = Support::with(['user'])->findOrFail($supportId);
        
        return response()->json([
            'success' => true,
            'support' => $support
        ]);
    }


}
