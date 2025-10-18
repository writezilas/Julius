<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\UserShare;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        
        if (view()->exists('user-panel.' . $request->path())) {

            return view('user-panel.' . $request->path());
        }
        return abort(404);
    }

    public function root()
    {
        $isTradeOpen = is_market_open();
        
        // Calculate paid referral earnings only
        $paidReferralEarnings = $this->calculatePaidReferralEarnings();
        
        // Get market timer data for the floating timer
        $marketTimerData = $this->getMarketTimerData();
        
        return view('user-panel.dashboard', compact('isTradeOpen', 'paidReferralEarnings', 'marketTimerData'));
    }
    
    /**
     * Calculate only the paid referral earnings for the current user
     * 
     * @return float
     */
    private function calculatePaidReferralEarnings()
    {
        // Get users who were referred by the current user, excluding self-referrals
        $refferals = User::where('refferal_code', auth()->user()->username)
            ->where('id', '!=', auth()->user()->id)
            ->where('username', '!=', auth()->user()->username)
            ->get();
        
        $paidEarnings = 0;
        
        // Check each referral to see if their bonus has been paid
        foreach ($refferals as $referral) {
            if ($referral->ref_amount > 0) {
                // Check if the referrer (current user) has sold bonus shares for THIS SPECIFIC referral
                $soldBonusShares = UserShare::where('user_id', auth()->user()->id)
                    ->where('get_from', 'refferal-bonus')
                    ->whereHas('invoice', function($invoiceQuery) use ($referral) {
                        // Link to the specific referral through the invoice reff_user_id
                        $invoiceQuery->where('reff_user_id', $referral->id);
                    })
                    ->where(function($shareQuery) {
                        // Check if this bonus share has been sold, completed, or paired and paid
                        $shareQuery->where('status', 'sold')
                                  ->orWhere('status', 'completed')
                                  ->orWhereExists(function($pairSubQuery) {
                                      // Check if the share has been paired and payment confirmed via user_share_pairs
                                      $pairSubQuery->select(DB::raw(1))
                                                   ->from('user_share_pairs')
                                                   ->where(function($whereClause) {
                                                       $whereClause->whereColumn('user_share_pairs.user_share_id', 'user_shares.id')
                                                                   ->orWhereColumn('user_share_pairs.paired_user_share_id', 'user_shares.id');
                                                   })
                                                   ->where('user_share_pairs.is_paid', 1);
                                  });
                    })
                    ->exists();
                    
                if ($soldBonusShares) {
                    $paidEarnings += $referral->ref_amount;
                }
            }
        }
        
        return $paidEarnings;
    }
    
    /**
     * Get market timer data for the floating timer card
     * 
     * @return array
     */
    private function getMarketTimerData()
    {
        $isMarketOpen = is_market_open();
        $closeTime = null;
        $appTimezone = get_app_timezone();
        
        // Only get close time if market is open
        if ($isMarketOpen) {
            $closeTime = get_current_market_close_time();
        }
        
        return [
            'isOpen' => $isMarketOpen,
            'closeTime' => $closeTime ? $closeTime->toISOString() : null,
            'timezone' => $appTimezone
        ];
    }

    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::find($id);
        $user->name = $request->get('name');
        $user->email = $request->get('email');

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar =  $avatarName;
        }

        $user->update();
        if ($user) {
            Session::flash('message', 'User Details Updated successfully!');
            Session::flash('alert-class', 'alert-success');
            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "User Details Updated successfully!"
            // ], 200); // Status code here
            return redirect()->back();
        } else {
            Session::flash('message', 'Something went wrong!');
            Session::flash('alert-class', 'alert-danger');
            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "Something went wrong!"
            // ], 200); // Status code here
            return redirect()->back();
        }
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Your Current password does not matches with the password you provided. Please try again."
            ], 200); // Status code
        } else {
            $user = User::find($id);
            $user->password = Hash::make($request->get('password'));
            $user->update();
            if ($user) {
                Session::flash('message', 'Password updated successfully!');
                Session::flash('alert-class', 'alert-success');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Password updated successfully!"
                ], 200); // Status code here
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('alert-class', 'alert-danger');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Something went wrong!"
                ], 200); // Status code here
            }
        }
    }
}
