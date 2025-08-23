<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Notifications\PaymentApproved;
use App\Notifications\PaymentSentToSeller;
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

            $data = $request->validate([
                'name'               => 'required',
                'user_share_id'      => 'required',
                'receiver_id'        => 'required',
                'sender_id'          => 'required',
                'number'             => 'required',
                'received_phone_no'  => 'required',
                'txs_id'             => 'required',
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

            $data = $request->validate([
                'name'               => 'required',
                'user_share_id'      => 'required',
                'user_share_pair_id' => 'required',
                'receiver_id'        => 'required',
                'sender_id'          => 'required',
                'number'             => 'required',
                'received_phone_no'  => 'required',
                'txs_id'             => 'required',
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
            
            //validate share pair and share amounts
            $sharePair = UserSharePair::findOrFail($payment->user_share_pair_id);
            
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
            $sharePair->save();

            //Update share count column
            $userShare = UserShare::findOrFail($sharePair->user_share_id);
            $userShare->increment('total_share_count', $sharePair->share);


            //finally update the paired share hold and share count column
            $pairedShare = UserShare::findOrFail($sharePair->paired_user_share_id);
            $pairedShare->decrement('hold_quantity', $sharePair->share);
            $pairedShare->increment('sold_quantity', $sharePair->share);

            //check if any paired share is unpaid

            if ($userShare->share_will_get == $userShare->total_share_count) {
                $userShare->status = 'completed';
                $userShare->start_date = date_format(now(), "Y/m/d H:i:s");
                $userShare->save();
            }
            if ($pairedShare->share_will_get == $pairedShare->sold_quantity) {
                $pairedShare->status = 'completed';
                $pairedShare->start_date = date_format(now(), "Y/m/d H:i:s");
                $pairedShare->save();
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
}
