<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Notifications\PaymentApproved;
use App\Notifications\PaymentSentToSeller;
use App\Services\PaymentDeclineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class UserSharePaymentController extends Controller
{
    public function sharesPayment(Request $request)
    {

        try {
            $currentUser = auth()->user();
            $share = UserShare::find($request->user_share_id);

            if ($share->status == 'failed') {
                toastr()->error('You can not make payment for failed share.');
                return back();
            }

            // Check if payment time has expired based on admin-configured deadline
            $hasExpiredPayments = false;
            $deadlineMinutes = $share->payment_deadline_minutes ?? 60; // fallback to 60 minutes
            
            foreach($request->user_share_pair_ids as $user_share_pair_id){
                $userSharePair = UserSharePair::find($user_share_pair_id);
                if ($userSharePair && \Carbon\Carbon::parse($userSharePair->created_at)->addMinutes($deadlineMinutes) < now()) {
                    $hasExpiredPayments = true;
                    break;
                }
            }
            
            if ($hasExpiredPayments) {
                toastr()->error('Payment time has expired. You can no longer make payment for this share.');
                return back();
            }

            $data = $request->validate([
                'name'               => 'required',
                'user_share_id'      => 'required',
                'receiver_id'        => 'required',
                'sender_id'          => 'required',
                'number'             => 'required',
                'received_phone_no'  => 'required',
                'txs_id'             => 'nullable|string|max:255',
                'amount'             => 'required',
                'note_by_sender'     => 'nullable',
            ]);

            $data['status'] = 'paid';

            foreach($request->user_share_pair_ids as $user_share_pair_id){
                $userSharePair              = UserSharePair::findOrFail($user_share_pair_id);
                
                // Prevent creating payment for 0 shares
                if ($userSharePair->share <= 0) {
                    \Log::warning('Attempted to create payment for 0 or negative shares. UserSharePair ID: ' . $user_share_pair_id);
                    continue; // Skip this pair
                }
                
                $data['user_share_pair_id'] = $user_share_pair_id;
                $amount                     = $userSharePair->share * $share->trade->price;
                $data['amount']             = $amount;

                $payment                    = UserSharePayment::create($data);

            if ($payment) {
                $user = User::find($request->receiver_id);

                // Reset payment failures for successful payment
                try {
                    $paymentFailureService = new \App\Services\PaymentFailureService();
                    $paymentFailureService->resetPaymentFailures($currentUser->id);
                } catch (\Exception $e) {
                    \Log::error('Error resetting payment failures: ' . $e->getMessage());
                }

                // Save log for payment receiver
                $log = new Log();
                $log->remarks = "You received a payment from " . $currentUser->username;
                $log->type    = "payment";
                $log->value   = $amount;
                $log->user_id = $user->id;
                $payment->logs()->save($log);

                // Save log for payers
                $log = new Log();
                $log->remarks = "You made a payment for " . $user->username;
                $log->type    = "payment";
                $log->value   = $amount;
                $log->user_id = $currentUser->id;
                $payment->logs()->save($log);

                    try {
                        Notification::send($user, new PaymentSentToSeller($payment));
                    } catch (\Exception $th) {
                        \Log::error('File:' . $th->getFile() . 'Line:' . $th->getLine() . 'Message:' . $th->getMessage());
                    }

                    // Pause the payment timer for the buyer's share when payment is submitted
                    try {
                        $buyerShare = UserShare::find($request->user_share_id);
                        if ($buyerShare) {
                            $enhancedTimerService = new \App\Services\EnhancedTimerManagementService();
                            $enhancedTimerService->pausePaymentTimer($buyerShare, 'Payment submitted - awaiting seller confirmation');
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error pausing payment timer after payment submission: ' . $e->getMessage());
                    }
                    
                    toastr()->success('Payment successfully completed. Kindly wait until the seller confirm payment.');
                    
                } else {
                    toastr()->error('Failed to payment. Please try again later');
                }
            }
            return back();
        } catch (\Exception $th) {
            \Log::error('File:' . $th->getFile() . 'Line:' . $th->getLine() . 'Message:' . $th->getMessage());
            toastr()->error('Failed to payment. Please try again later');
            return back();
        }
    }
    
    public function payment(Request $request)
    {
        try {
            $share = UserShare::findOrFail($request->user_share_id);

            if ($share->status == 'failed') {
                toastr()->error('You can not make payment for failed share.');
                return back();
            }

            // Check if payment time has expired based on admin-configured deadline
            $userSharePair = UserSharePair::findOrFail($request->user_share_pair_id);
            $deadlineMinutes = $share->payment_deadline_minutes ?? 60; // fallback to 60 minutes
            
            if (\Carbon\Carbon::parse($userSharePair->created_at)->addMinutes($deadlineMinutes) < now()) {
                toastr()->error('Payment time has expired. You can no longer make payment for this share.');
                return back();
            }

            $data = $request->validate([
                'name'               => 'required',
                'user_share_id'      => 'required',
                'user_share_pair_id' => 'required',
                'receiver_id'        => 'required',
                'sender_id'          => 'required',
                'number'             => 'required',
                'received_phone_no'  => 'required',
                'txs_id'             => 'nullable|string|max:255',
                'amount'             => 'required',
                'note_by_sender'     => 'nullable',
            ]);

            // Validate the share pair to prevent 0 share payments
            $userSharePair = UserSharePair::findOrFail($request->user_share_pair_id);
            if ($userSharePair->share <= 0) {
                \Log::warning('Attempted to create payment for 0 or negative shares. UserSharePair ID: ' . $request->user_share_pair_id);
                toastr()->error('Cannot create payment for 0 shares.');
                return back();
            }

            $data['status'] = 'paid';
            $payment = UserSharePayment::create($data);

            if ($payment) {
                $user = User::findOrFail($request->receiver_id);

                // Save log for payment receiver
                $log = new Log();
                $log->remarks = "You received a payment from " . auth()->user()->username;
                $log->type = "payment";
                $log->value = $data['amount'];
                $log->user_id = $user->id;
                $payment->logs()->save($log);

                // Save log for payers
                $log = new Log();
                $log->remarks = "You made a payment for " . $user->username;
                $log->type = "payment";
                $log->value = $data['amount'];
                $log->user_id = auth()->user()->id;
                $payment->logs()->save($log);

                try {
                    Notification::send($user, new PaymentSentToSeller($payment));
                } catch (\Exception $th) {
                    \Log::error('File:' . $th->getFile() . 'Line:' . $th->getLine() . 'Message:' . $th->getMessage());
                }

                // Pause the payment timer for the buyer's share when payment is submitted
                try {
                    $buyerShare = UserShare::find($request->user_share_id);
                    if ($buyerShare) {
                        $enhancedTimerService = new \App\Services\EnhancedTimerManagementService();
                        $enhancedTimerService->pausePaymentTimer($buyerShare, 'Payment submitted - awaiting seller confirmation');
                    }
                } catch (\Exception $e) {
                    \Log::error('Error pausing payment timer after payment submission: ' . $e->getMessage());
                }
                
                toastr()->success('Payment successfully completed. Kindly wait until the seller confirm payment.');
                
            } else {
                toastr()->error('Failed to payment. Please try again later');
            }
            return back();
        } catch (\Exception $th) {
            \Log::error('File:' . $th->getFile() . 'Line:' . $th->getLine() . 'Message:' . $th->getMessage());
            toastr()->error('Failed to payment. Please try again later');
            return back();
        }
    }

    public function paymentApprove(Request $request)
    {
        try {
            DB::beginTransaction();
            //update payment status column
            $payment = UserSharePayment::findOrFail($request->paymentId);
            
            // Check if payment is already processed
            if ($payment->status === 'conformed') {
                DB::rollBack();
                toastr()->error('Payment has already been confirmed.');
                return back();
            }
            
            //validate share pair and share amounts
            $sharePair = UserSharePair::findOrFail($payment->user_share_pair_id);
            
            // Check if share pair is already processed
            if ($sharePair->is_paid == 1) {
                DB::rollBack();
                toastr()->error('This share pair has already been processed.');
                return back();
            }
            
            // Prevent processing payment for 0 shares
            if ($sharePair->share <= 0) {
                \Log::warning('Attempted to approve payment for 0 or negative shares. Payment ID: ' . $payment->id . ', SharePair ID: ' . $sharePair->id);
                DB::rollBack();
                toastr()->error('Cannot approve payment for 0 shares.');
                return back();
            }
            
            $payment->note_by_receiver = $request->note_by_receiver;
            $payment->status = 'conformed';
            if($request->by_admin){
                $payment->by_admin = 1;
            }
            $payment->save();

            //update share pair payment column
            $sharePair->is_paid = 1;
            
            // Reset decline attempts on successful payment approval
            $paymentDeclineService = new PaymentDeclineService();
            $paymentDeclineService->resetDeclineAttempts($sharePair->id);
            
            $sharePair->save();

            //Update share count column
            $userShare = UserShare::findOrFail($sharePair->user_share_id);
            $userShare->increment('total_share_count', $sharePair->share);


            //finally update the paired share hold and share count column
            $pairedShare = UserShare::findOrFail($sharePair->paired_user_share_id);
            
            // Additional validation: Check if the paired share is actually a completed buyer order
            if ($pairedShare->status === 'completed' && $pairedShare->total_share_count === $pairedShare->share_will_get && $pairedShare->hold_quantity === 0) {
                \Log::error("Attempted to process payment for a completed buyer order. Paired Share Ticket: {$pairedShare->ticket_no}, SharePair ID: {$sharePair->id}, Payment ID: {$payment->id}");
                DB::rollBack();
                toastr()->error('Cannot process payment: The seller order has already been completed. Please contact support to resolve this pairing issue.');
                return back();
            }
            
            // Prevent constraint violation by checking if hold_quantity has enough shares
            if ($pairedShare->hold_quantity < $sharePair->share) {
                \Log::error("Cannot decrement hold_quantity. Current hold_quantity: {$pairedShare->hold_quantity}, trying to decrement: {$sharePair->share}, SharePair ID: {$sharePair->id}, Payment ID: {$payment->id}");
                DB::rollBack();
                toastr()->error('Payment confirmation failed due to insufficient hold quantity. Please contact support.');
                return back();
            }
            
            $pairedShare->decrement('hold_quantity', $sharePair->share);
            $pairedShare->increment('sold_quantity', $sharePair->share);

            //check if any paired share is unpaid

            if ($userShare->share_will_get == $userShare->total_share_count) {
                $userShare->status = 'completed';
                $userShare->start_date = date_format(now(), "Y/m/d H:i:s");
                
                // CRITICAL: Clear payment timer AND start selling timer when payment is confirmed
                // Investment period begins when seller confirms money received, not when buyer submits
                try {
                    $enhancedTimerService = new \App\Services\EnhancedTimerManagementService();
                    
                    // Clear payment timer - payment phase is complete
                    $userShare->update([
                        'payment_timer_paused' => 0,
                        'payment_timer_paused_at' => null,
                        // Also clear legacy timer fields for backward compatibility
                        'timer_paused' => 0,
                        'timer_paused_at' => null
                    ]);
                    
                    // Start selling timer (investment maturity) - this is when investment period begins
                    if ($userShare->get_from === 'purchase') {
                        $enhancedTimerService->startSellingTimer($userShare, 'Payment confirmed by seller - investment period starts now');
                        
                        \Log::info("Investment period started for buyer share {$userShare->ticket_no} after payment confirmation", [
                            'share_id' => $userShare->id,
                            'timer_type' => 'investment_started',
                            'investment_period_days' => $userShare->period,
                            'selling_started_at' => now()->toDateTimeString()
                        ]);
                    }
                    
                    \Log::info("Payment timer cleared for buyer share {$userShare->ticket_no} after payment confirmation", [
                        'share_id' => $userShare->id,
                        'timer_type' => 'payment_cleared',
                        'investment_timer_started' => ($userShare->get_from === 'purchase')
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error("Failed to manage timers for share {$userShare->id}: " . $e->getMessage());
                    // Continue processing even if timer management fails
                }
                
                $userShare->save();
            }
            // Check if seller share should be marked as sold (all shares sold)
            if ($pairedShare->total_share_count == 0 && $pairedShare->hold_quantity == 0 && $pairedShare->sold_quantity > 0) {
                $pairedShare->status = 'sold';
                $pairedShare->is_sold = 1;
                $pairedShare->save();
                \Log::info('Seller share marked as sold: ' . $pairedShare->ticket_no . ' (sold_quantity: ' . $pairedShare->sold_quantity . ')');
            } else {
                // Check if seller has any other unpaid pairs
                $otherUnpaidPairs = UserSharePair::where('paired_user_share_id', $pairedShare->id)
                    ->where('id', '!=', $sharePair->id)
                    ->where('is_paid', 0)
                    ->exists();
                
                // Only mark as completed if no other unpaid pairs exist and not fully sold
                if (!$otherUnpaidPairs && $pairedShare->status !== 'sold') {
                    $pairedShare->status = 'completed';
                    $pairedShare->save();
                }
            }

            // send notification
            $user = User::findOrFail($payment->sender_id);
            try {
                Notification::send($user, new PaymentApproved($payment));
            } catch (\Exception $th) {
                \Log::error('File:' . $th->getFile() . 'Line:' . $th->getLine() . 'Message:' . $th->getMessage());
            }


            // Save log for payment receiver
            $log = new Log();
            $log->remarks = "You confirmed a payment from " . $user->username;
            $log->type = "payment";
            $log->value = $payment->amount;
            $log->user_id = auth()->user()->id;
            $payment->logs()->save($log);

            // Save log for payers
            $log = new Log();
            $log->remarks = "Your payment is confirmed by " . auth()->user()->username;
            $log->type = "payment";
            $log->value = $payment->amount;
            $log->user_id = $user->id;
            $payment->logs()->save($log);

            if ($userShare->status == 'failed') {
                saveAllocateShare($userShare->user_id, $userShare, $sharePair->share);
            }

            DB::commit();
            toastr()->success('Payment received status updated successfully.');
        } catch (\Exception $e) {
            \Log::error('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            DB::rollBack();
            toastr()->error('Failed to confirm payment receive. Please try again later');
        }
        return back();
    }

    public function paymentDecline(Request $request)
    {
        try {
            // Find the payment to decline
            $payment = UserSharePayment::findOrFail($request->paymentId);
            
            // Validate share pair and share amounts
            $sharePair = UserSharePair::findOrFail($payment->user_share_pair_id);
            
            // Prevent processing payment for 0 shares
            if ($sharePair->share <= 0) {
                \Log::warning('Attempted to decline payment for 0 or negative shares. Payment ID: ' . $payment->id . ', SharePair ID: ' . $sharePair->id);
                toastr()->error('Cannot decline payment for 0 shares.');
                return back();
            }
            
            // Get decline reason and admin flag
            $declineReason = $request->note_by_receiver ?? $request->admin_comment;
            $byAdmin = $request->by_admin || isset($request->admin_comment);
            
            // Use PaymentDeclineService to handle the decline logic with second chance
            $paymentDeclineService = new PaymentDeclineService();
            $result = $paymentDeclineService->handlePaymentDecline($payment, $declineReason, $byAdmin);
            
            if ($result['success']) {
                // Create logs for the decline action
                $sender = $payment->sender;
                $receiver = auth()->user();
                
                // Save log for payment receiver (who declined)
                $log = new Log();
                $log->remarks = $result['is_final_decline'] 
                    ? "You permanently declined a payment from " . $sender->username 
                    : "You declined a payment from " . $sender->username . " (second chance given)";
                $log->type = "payment";
                $log->value = $payment->amount;
                $log->user_id = $receiver->id;
                $payment->logs()->save($log);

                // Save log for payment sender (who got declined) 
                $log = new Log();
                $log->remarks = $result['is_final_decline']
                    ? "Your payment was permanently declined by " . $receiver->username . ". You will be re-matched with a new seller."
                    : "Your payment was declined by " . $receiver->username . ". Please verify and reconfirm your payment.";
                $log->type = "payment";
                $log->value = $payment->amount;
                $log->user_id = $sender->id;
                $payment->logs()->save($log);
                
                // Show appropriate success message
                if ($result['is_final_decline']) {
                    toastr()->success('Payment permanently declined. The buyer will be automatically re-matched with a new seller.');
                } else {
                    toastr()->success('Payment declined. The buyer has been notified and given a second chance to confirm payment.');
                }
            } else {
                toastr()->error($result['message'] ?? 'Failed to decline payment. Please try again later.');
            }
            
        } catch (\Exception $e) {
            \Log::error('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            toastr()->error('Failed to decline payment. Please try again later.');
        }
        
        return back();
    }
}
