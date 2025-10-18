<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserShare;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    /**
     * Display the referrals page
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $pageTitle = __('translation.refferals');
        
        // Get users who were referred by the current user, excluding self-referrals
        $refferals = User::where('refferal_code', \auth()->user()->username)
            ->where('id', '!=', \auth()->user()->id) // Exclude self
            ->where('username', '!=', \auth()->user()->username) // Double safety
            ->latest()->get();
        
        // Calculate referral statistics with correct payment status logic
        $totalReferrals = $refferals->count();
        $totalEarnings = $refferals->sum('ref_amount');
        
        // Count paid vs pending based on whether referrer's bonus shares have been sold
        $paidReferrals = 0;
        $pendingPayments = 0;
        
        // Add status information to each referral
        foreach ($refferals as $referral) {
            if ($referral->ref_amount > 0) {
                // Check if the referrer (current user) has sold bonus shares for THIS SPECIFIC referral
                // We need to find the referral bonus share that was created for this specific referral
                // and check if it has been sold and payment confirmed
                $soldBonusShares = UserShare::where('user_id', \auth()->user()->id)
                    ->where('get_from', 'refferal-bonus')
                    ->whereHas('invoice', function($invoiceQuery) use ($referral) {
                        // Link to the specific referral through the invoice reff_user_id
                        // The referral user is stored in reff_user_id (the person who was referred)
                        $invoiceQuery->where('reff_user_id', $referral->id);
                    })
                    ->where(function($shareQuery) {
                        // Check if this bonus share has been sold and payment confirmed
                        $shareQuery->where('status', 'sold')
                                  ->orWhere(function($pairQuery) {
                                      $pairQuery->whereHas('pairedWithThis', function($pairSubQuery) {
                                          $pairSubQuery->where('is_paid', 1)
                                                      ->whereHas('payment', function($paymentQuery) {
                                                          $paymentQuery->where('status', 'paid');
                                                      });
                                      });
                                  });
                    })
                    ->exists();
                    
                if ($soldBonusShares) {
                    $referral->payment_status = 'paid';
                    $paidReferrals++;
                } else {
                    $referral->payment_status = 'pending';
                    $pendingPayments++;
                }
            } else {
                $referral->payment_status = 'pending'; // No ref_amount set yet
                $pendingPayments++;
            }
        }
        
        // Recent referrals (last 7 days)
        $recentReferrals = $refferals->filter(function($referral) {
            return $referral->created_at >= now()->subDays(7);
        })->count();
        
        return view('user-panel.referrals', compact(
            'pageTitle', 
            'refferals', 
            'totalReferrals',
            'totalEarnings',
            'pendingPayments',
            'paidReferrals',
            'recentReferrals'
        ));
    }
}
