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
    public function payment(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'user_share_id' => 'required',
            'user_share_pair_id' => 'required',
            'receiver_id' => 'required',
            'sender_id' => 'required',
            'number' => 'required',
            'received_phone_no' => 'required',
            'txs_id' => 'required',
            'amount' => 'required',
            'note_by_sender' => 'nullable',
        ]);

        $data['status'] = 'paid';
        $payment = UserSharePayment::create($data);

        if($payment) {
            $user = User::findOrFail($request->receiver_id);

            // Save log for payment receiver
            $log = new Log();
            $log->remarks = "You received a payment from ". auth()->user()->username;
            $log->type = "payment";
            $log->value = $data['amount'];
            $log->user_id = $user->id;
            $payment->logs()->save($log);

            // Save log for payers
            $log = new Log();
            $log->remarks = "You made a payment for ". $user->username;
            $log->type = "payment";
            $log->value = $data['amount'];
            $log->user_id = auth()->user()->id;
            $payment->logs()->save($log);


            Notification::send($user, new PaymentSentToSeller($payment));

            toastr()->success('Payment successfully completed. Please wait until the seller conform it.');
        }else {
            toastr()->error('Failed to payment. Please try again later');
        }
        return back();
    }

    public function paymentApprove(Request $request)
    {
        try {
            DB::beginTransaction();
            //update payment status column
            $payment = UserSharePayment::findOrFail($request->paymentId);
            $payment->note_by_receiver = $request->note_by_receiver;
            $payment->status = 'conformed';
            $payment->save();

            //update share pair payment column
            $sharePair = UserSharePair::findOrFail($payment->user_share_pair_id);
            $sharePair->is_paid = 1;
            $sharePair->save();

            //Update share count column
            $userShare = UserShare::findOrFail($sharePair->user_share_id);
            $userShare->total_share_count = abs($userShare->total_share_count + $sharePair->share);
            $userShare->save();

            //finally update the paired share hold and share count column
            $pairedShare = UserShare::findOrFail($sharePair->paired_user_share_id);
            $pairedShare->hold_quantity = abs($pairedShare->hold_quantity - $sharePair->share);
            $pairedShare->save();

            //check if any paired share is unpaid
            if($pairedShare->save()) {

                $unpaidPaires = UserSharePair::where('user_share_id', $userShare->id)->where('is_paid', 0)->exists();

                if(!$unpaidPaires) {
                    $userShare->status = 'completed';
                    $userShare->start_date = date_format(now(),"Y/m/d H:i:s");
                    $userShare->save();
                }

                // send notification
                $user = User::findOrFail($payment->sender_id);
                Notification::send($user, new PaymentApproved($payment));

                // Save log for payment receiver
                $log = new Log();
                $log->remarks = "You confirmed a payment from ". $user->username;
                $log->type = "payment";
                $log->value = $payment->amount;
                $log->user_id = auth()->user()->id;
                $payment->logs()->save($log);

                // Save log for payers
                $log = new Log();
                $log->remarks = "Your payment is confirmed by ". auth()->user()->username;
                $log->type = "payment";
                $log->value = $payment->amount;
                $log->user_id = $user->id;
                $payment->logs()->save($log);
            }

            DB::commit();
            toastr()->success('Payment received status updated successfully.');
        }catch (\Exception $e) {
            \Log::error('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            DB::rollBack();
            toastr()->error('Failed to confirm payment receive. Please try again later');
        }
        return back();
    }


}
