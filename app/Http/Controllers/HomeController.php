<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Policy;
use App\Models\Trade;
use App\Models\User;
use App\Models\UserShare;
use App\Models\UserSharePair;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{

    //    /**
    //     * Show the application dashboard.
    //     *
    //     * @return \Illuminate\Contracts\Support\Renderable
    //     */
    //    public function index(Request $request)
    //    {
    //        if(auth()->user()->role_id != 2){
    ////            if (view()->exists($request->path())) {
    ////                return view($request->path());
    ////            }
    ////            return abort(404);
    //            return view('index');
    //        }else{
    //
    //            if (view()->exists('user-panel.'.$request->path())) {
    //                return view('user-panel.'.$request->path());
    //            }
    //            return abort(404);
    //        }
    //        return abort(404);
    //    }

    public function index(Request $request)
    {
        $trades = Trade::with('userShares')->whereStatus(1)->get();

        $recentShares = UserShare::latest()->limit(5)->get();
        $topCategory = Trade::with('userShares')
                    ->withCount('userShares')
                    ->whereStatus(1)->orderBy('user_shares_count', 'DESC')->get();

        $topTraders = User::where('role_id', 2)->orderBy('balance', 'DESC')->limit(5)->get();

        $pendingShares = UserSharePair::with('pairedUserShare:id,status,ticket_no,trade_id', 
            'pairedShare:id,user_id,status,ticket_no,trade_id', 'pairedShare.user', 'payment')
            ->where('is_paid', 0)
            ->whereHas('payment')
            ->orderBy('id')->get();

        // New User Activity Data for Right Card
        // New users - recently signed up (last 7 days) - TOP 5
        $newUsers = User::where('role_id', 2)
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(5)
            ->get();

        // New traders - users who made their first investment (recently) - TOP 5
        $newTraders = User::where('role_id', 2)
            ->whereHas('shares', function($query) {
                $query->where('status', '!=', 'failed');
            })
            ->withCount('shares')
            ->having('shares_count', '>', 0)
            ->latest()
            ->limit(5)
            ->get();

        // Top investors overall - users with highest balance - TOP 5
        $topInvestors = User::where('role_id', 2)
            ->where('balance', '>', 0)
            ->orderBy('balance', 'DESC')
            ->limit(5)
            ->get();

        // Top referral users - users who have referred the most users - TOP 5
        $topReferralUsers = User::select('users.id', 'users.name', 'users.username', 'users.avatar', 'users.created_at', DB::raw('COUNT(referred_users.id) as referral_count'))
            ->leftJoin('users as referred_users', 'users.username', '=', 'referred_users.refferal_code')
            ->where('users.role_id', 2)
            ->where('referred_users.id', '!=', DB::raw('users.id')) // Exclude self-referrals
            ->groupBy('users.id', 'users.name', 'users.username', 'users.avatar', 'users.created_at')
            ->having('referral_count', '>', 0)
            ->orderBy('referral_count', 'DESC')
            ->limit(5)
            ->get();

        // return $pendingShares;
        
        return view('index', compact('trades', 'recentShares', 'topCategory', 'topTraders', 'pendingShares', 'newUsers', 'newTraders', 'topInvestors', 'topReferralUsers'));
    }

    public function root()
    {          
        $isTradeOpen = true;
        
        // Calculate paid referral earnings only
        $paidReferralEarnings = $this->calculatePaidReferralEarnings();
        
        return view('user-panel.dashboard', compact('isTradeOpen', 'paidReferralEarnings'));
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
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::find($id);
        $user->name = $request->get('name');

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar =  'images/' .$avatarName;
        }


        if ($user->update()) {
            toastr()->success('User profile details Updated successfully!');
            $log = new Log();
            $log->remarks = "profile updated successfully";
            $log->type    = "update_profile";
            $log->value   = 0;
            $log->user_id = $user->id;
            $user->logs()->save($log);
        } else {
            toastr()->error('Failed to update user profile');
        }
        return back();
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        // Check if current password matches
        if (!Hash::check($request->get('current_password'), Auth::user()->password)) {
            toastr()->error('Your current password does not match with the password you provided. Please try again.');
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        try {
            $user = User::find($id);
            if (!$user) {
                toastr()->error('User not found.');
                return back();
            }

            // Update password
            $user->password = Hash::make($request->get('password'));
            
            if ($user->save()) {
                toastr()->success('Your password has been updated successfully!');
                
                // Create log entry
                $log = new Log();
                $log->remarks = "Password updated successfully";
                $log->type    = "update_password";
                $log->value   = 0;
                $log->user_id = $user->id;
                $user->logs()->save($log);
            } else {
                toastr()->error('Failed to update password. Please try again.');
            }
        } catch (\Exception $e) {
            \Log::error('Password update error: ' . $e->getMessage());
            toastr()->error('An error occurred while updating your password. Please try again.');
        }

        return back();
    }

    public function updateBusinessProfile(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                toastr()->error('User not found.');
                return back();
            }

            // Ensure user can only update their own profile
            if (auth()->user()->id != $user->id) {
                toastr()->error('You can only update your own profile.');
                return back();
            }

            $canEditCore = $this->canEditCoreBusinessProfile($user);
            $canEditSupplementary = $this->canEditSupplementaryFields($user);

            // If user cannot edit anything, return error
            if (!$canEditCore && !$canEditSupplementary) {
                toastr()->error('Business profile cannot be modified.');
                return back();
            }

            // Build validation rules based on what can be edited
            $validationRules = [];
            
            if ($canEditCore) {
                $validationRules['business_account_id'] = ['required', 'integer', 'in:1,2'];
                $validationRules['mpesa_no'] = ['required', 'string', 'max:20'];
                $validationRules['mpesa_name'] = ['required', 'string', 'max:255'];
            }
            
            if ($canEditSupplementary) {
                $validationRules['mpesa_till_no'] = ['nullable', 'string', 'max:20'];
                $validationRules['mpesa_till_name'] = ['nullable', 'string', 'max:255'];
                $validationRules['trading_category_id'] = ['nullable', 'integer', 'exists:trades,id'];
            }

            // Validate the input
            $request->validate($validationRules);

            // Get existing business profile data
            $existingProfile = $user->getBusinessProfileData();
            
            // Update only the fields that can be edited
            if ($canEditCore) {
                $existingProfile['mpesa_no'] = $request->get('mpesa_no');
                $existingProfile['mpesa_name'] = $request->get('mpesa_name');
                $user->business_account_id = $request->get('business_account_id');
            }
            
            if ($canEditSupplementary) {
                $existingProfile['mpesa_till_no'] = $request->get('mpesa_till_no');
                $existingProfile['mpesa_till_name'] = $request->get('mpesa_till_name');
                $user->trading_category_id = $request->get('trading_category_id');
            }

            // Update user data
            $user->business_profile = json_encode($existingProfile);

            if ($user->save()) {
                toastr()->success('Business profile updated successfully!');
                
                // Create log entry
                $log = new Log();
                $log->remarks = "Business profile updated successfully";
                $log->type    = "update_business_profile";
                $log->value   = 0;
                $log->user_id = $user->id;
                $user->logs()->save($log);
            } else {
                toastr()->error('Failed to update business profile. Please try again.');
            }
        } catch (\Exception $e) {
            \Log::error('Business profile update error: ' . $e->getMessage());
            toastr()->error('An error occurred while updating your business profile. Please try again.');
        }

        return back();
    }

    /**
     * Check if user can edit core business profile fields (mpesa_no, mpesa_name)
     * Returns true if business_profile is empty/null or contains empty core values
     */
    private function canEditCoreBusinessProfile($user)
    {
        // If business_profile is null or empty, user can edit
        if (empty($user->business_profile)) {
            return true;
        }

        try {
            $profile = json_decode($user->business_profile, true);
            
            // If JSON decode failed, allow editing
            if (!$profile || !is_array($profile)) {
                return true;
            }

            // Check if core required fields are empty
            $coreFields = ['mpesa_no', 'mpesa_name'];
            foreach ($coreFields as $field) {
                if (!empty($profile[$field])) {
                    return false; // At least one core field is filled, cannot edit core fields
                }
            }

            // All core fields are empty, can edit
            return true;
        } catch (\Exception $e) {
            // If there's an error parsing, allow editing
            return true;
        }
    }

    /**
     * Check if user can edit supplementary business profile fields
     * (mpesa_till_no, mpesa_till_name, trading_category)
     * These can only be edited if they are currently empty/null
     */
    private function canEditSupplementaryFields($user)
    {
        // Check if trading_category_id is empty
        if (!empty($user->trading_category_id)) {
            return false; // Trading category already set
        }

        // Check business profile for till fields
        try {
            $profile = $user->getBusinessProfileData();
            
            // Check if till fields are already filled
            if (!empty($profile['mpesa_till_no']) || !empty($profile['mpesa_till_name'])) {
                return false; // At least one till field is filled
            }
            
            return true; // All supplementary fields are empty, can edit
        } catch (\Exception $e) {
            // If there's an error, allow editing
            return true;
        }
    }

    /**
     * Legacy method for backward compatibility
     * Now checks if user can edit ANY business profile fields
     */
    private function canEditBusinessProfile($user)
    {
        return $this->canEditCoreBusinessProfile($user) || $this->canEditSupplementaryFields($user);
    }

    public function profile()
    {
        if (\auth()->user()->role_id === 2) {
            $trades = Trade::where('status', 1)->get();
            $canEditBusinessProfile = $this->canEditBusinessProfile(auth()->user());
            $canEditCoreProfile = $this->canEditCoreBusinessProfile(auth()->user());
            $canEditSupplementaryFields = $this->canEditSupplementaryFields(auth()->user());
            
            // Load the user with trading category relationship for proper display
            $user = auth()->user()->load('tradingCategory', 'trade');
            
            return view('user-panel.profile', compact('trades', 'canEditBusinessProfile', 'canEditCoreProfile', 'canEditSupplementaryFields'));
        } else {
            $pageTitle = 'Admin profile';
            return view('admin-panel.settings.profile', compact('pageTitle'));
        }
    }

    public function referrals()
    {
        $pageTitle = __('translation.refferals');
        // Get users who were referred by the current user, excluding self-referrals
        $refferals = User::where('refferal_code', \auth()->user()->username)
            ->where('id', '!=', \auth()->user()->id) // Exclude self
            ->where('username', '!=', \auth()->user()->username) // Double safety
            ->latest()->get();
        
        // Calculate referral statistics
        $totalReferrals = $refferals->count();
        $totalEarnings = $refferals->sum('ref_amount');
        $pendingPayments = $refferals->where('ref_amount', 0)->count();
        $paidReferrals = $refferals->where('ref_amount', '>', 0)->count();
        
        // Recent referrals (last 7 days)
        $recentReferrals = $refferals->filter(function($referral) {
            return $referral->created_at >= now()->subDays(7);
        })->count();
        
        return view('user-panel.refferals', compact(
            'pageTitle', 
            'refferals', 
            'totalReferrals',
            'totalEarnings',
            'pendingPayments',
            'paidReferrals',
            'recentReferrals'
        ));
    }
    public function boughtShares()
    {
        $pageTitle = __('translation.boughtshares');

        // Get paginated results (10 items per page) - show bought shares + admin allocations
        $boughtShares = UserShare::where('user_id', \auth()->user()->id)
            ->where(function($query) {
                // Include shares purchased by the user
                $query->where('get_from', 'purchase')
                      ->whereIn('status', ['pending', 'paired', 'completed', 'failed']);
            })
            ->orWhere(function($query) {
                // ALSO include admin-allocated shares so they appear in bought shares view
                $query->where('user_id', \auth()->user()->id)
                      ->where('get_from', 'allocated-by-admin')
                      ->whereIn('status', ['completed']); // Only show completed admin allocations
            })
            ->orderBy('id', 'DESC')
            ->paginate(10);

        // Get statistics for all bought shares (not just current page)
        $allShares = UserShare::where('user_id', \auth()->user()->id)
            ->where(function($query) {
                // Include shares purchased by the user
                $query->where('get_from', 'purchase')
                      ->whereIn('status', ['pending', 'paired', 'completed', 'failed']);
            })
            ->orWhere(function($query) {
                // ALSO include admin-allocated shares in statistics
                $query->where('user_id', \auth()->user()->id)
                      ->where('get_from', 'allocated-by-admin')
                      ->whereIn('status', ['completed']); // Only show completed admin allocations
            })
            ->get();
        $totalShares = $allShares->count();
        $activeShares = $allShares->where('status', 'paired')->count();
        $completedShares = $allShares->where('status', 'completed')->count();
        $failedShares = $allShares->where('status', 'failed')->count();
        // Only include completed, paired, and pending shares in investment calculation (exclude failed)
        $totalInvestment = $allShares->whereNotIn('status', ['failed'])->sum('amount');

        return view('user-panel.bought-shares', compact(
            'pageTitle', 
            'boughtShares', 
            'totalShares', 
            'activeShares', 
            'completedShares', 
            'failedShares', 
            'totalInvestment'
        ));
    }

    public function soldShares(Request $request)
    {   
        $pageTitle = __('translation.soldshares');

        // Apply share type filter (bought vs sold)
        $baseQuery = UserShare::with('trade')
            ->where('user_id', \auth()->user()->id);
        
        if ($request->filled('share_type') && $request->share_type !== 'all') {
            if ($request->share_type === 'bought') {
                // Show only purchased shares that are in selling/maturity phase
                $baseQuery->where('get_from', 'purchase')
                         ->where('status', 'completed')
                         ->whereNotNull('start_date')
                         ->where('start_date', '!=', '');
            } elseif ($request->share_type === 'sold') {
                // Show only shares being sold (excluding purchased shares)
                $baseQuery->where('get_from', '!=', 'purchase');
            }
        }
        
        // Show shares in their selling/maturity phase - FIXED QUERY STRUCTURE
        $query = clone $baseQuery;
        $soldShares = $query->where(function($query) {
                // Group ALL conditions under a single WHERE to ensure proper user_id scoping
                $query->where(function($subQuery) {
                    // Show shares that have buyers (traditional selling)
                    $subQuery->where('get_from', '!=', 'purchase')
                           ->whereHas('pairedWithThis')
                           ->whereIn('status', ['completed', 'sold', 'paired'])
                           ->whereNotNull('start_date')
                           ->where('start_date', '!=', '');
                })
                ->orWhere(function($subQuery) {
                    // OR show matured shares that are ready to sell (excluding buyer trades)
                    $subQuery->where('get_from', '!=', 'purchase')
                           ->where('is_ready_to_sell', 1)
                           ->whereIn('status', ['completed', 'sold', 'paired'])
                           ->whereNotNull('start_date')
                           ->where('start_date', '!=', '');
                })
                ->orWhere(function($subQuery) {
                    // OR show admin-allocated shares (they start directly in selling/maturity phase)
                    $subQuery->where('get_from', 'allocated-by-admin')
                           ->whereIn('status', ['completed', 'sold']) // Include both completed and sold
                           ->whereNotNull('start_date')
                           ->where('start_date', '!=', '')
                           ->whereNotNull('selling_started_at'); // Ensure timer can run
                })
                ->orWhere(function($subQuery) {
                    // OR show purchased shares that are in maturity countdown phase
                    $subQuery->where('get_from', 'purchase')
                           ->where('status', 'completed')
                           ->where('is_ready_to_sell', 0) // Still in countdown
                           ->whereNotNull('start_date')
                           ->where('start_date', '!=', '');
                })
                ->orWhere(function($subQuery) {
                    // OR show purchased shares that have matured and are ready to sell (KEY FOR ELSIE)
                    $subQuery->where('get_from', 'purchase')
                           ->where('status', 'completed')
                           ->where('is_ready_to_sell', 1) // Ready to sell
                           ->whereNotNull('start_date')
                           ->where('start_date', '!=', '');
                })
                ->orWhere(function($subQuery) {
                    // OR show shares with active buyer pairings (forced maturation fix)
                    // This handles cases where shares were forced to mature but still have pending payments
                    $subQuery->whereHas('pairedWithThis', function($pairQuery) {
                              $pairQuery->where('is_paid', 0)
                                       ->whereHas('payment', function($paymentQuery) {
                                           $paymentQuery->where('status', 'paid');
                                       });
                           })
                           ->whereIn('status', ['completed', 'sold', 'paired'])
                           ->whereNotNull('start_date')
                           ->where('start_date', '!=', '');
                })
                ->orWhere(function($subQuery) {
                    // OR show matured referral bonuses that have been paired with buyers
                    $subQuery->where('get_from', 'refferal-bonus')
                           ->where('status', 'completed')
                           ->whereNotNull('matured_at') // Only matured bonuses
                           ->where('is_ready_to_sell', 1) // Ready to sell
                           ->whereHas('pairedWithThis'); // Only paired bonuses
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Get statistics for all shares available for selling (not just current page)
        $statsQuery = clone $baseQuery;
        $allSoldShares = $statsQuery->where(function($query) {
            // Apply the same logic for statistics as the main query
            $query->where(function($subQuery) {
                // Show shares that have buyers (traditional selling)
                $subQuery->where('get_from', '!=', 'purchase')
                       ->whereHas('pairedWithThis')
                       ->whereIn('status', ['completed', 'sold', 'paired'])
                       ->whereNotNull('start_date')
                       ->where('start_date', '!=', '');
            })
            ->orWhere(function($subQuery) {
                // OR show matured shares that are ready to sell (excluding buyer trades)
                $subQuery->where('get_from', '!=', 'purchase')
                       ->where('is_ready_to_sell', 1)
                       ->whereIn('status', ['completed', 'sold', 'paired'])
                       ->whereNotNull('start_date')
                       ->where('start_date', '!=', '');
            })
            ->orWhere(function($subQuery) {
                // OR show admin-allocated shares (they start directly in selling/maturity phase)
                $subQuery->where('get_from', 'allocated-by-admin')
                       ->whereIn('status', ['completed', 'sold'])
                       ->whereNotNull('start_date')
                       ->where('start_date', '!=', '')
                       ->whereNotNull('selling_started_at'); // Ensure timer can run
            })
            ->orWhere(function($subQuery) {
                // OR show purchased shares in countdown
                $subQuery->where('get_from', 'purchase')
                       ->where('status', 'completed')
                       ->where('is_ready_to_sell', 0)
                       ->whereNotNull('start_date')
                       ->where('start_date', '!=', '');
            })
            ->orWhere(function($subQuery) {
                // OR show purchased shares that have matured and are ready to sell
                $subQuery->where('get_from', 'purchase')
                       ->where('status', 'completed')
                       ->where('is_ready_to_sell', 1) // Ready to sell
                       ->whereNotNull('start_date')
                       ->where('start_date', '!=', '');
            })
            ->orWhere(function($subQuery) {
                // OR show shares with active buyer pairings
                $subQuery->whereHas('pairedWithThis', function($pairQuery) {
                          $pairQuery->where('is_paid', 0)
                                   ->whereHas('payment', function($paymentQuery) {
                                       $paymentQuery->where('status', 'paid');
                                   });
                       })
                       ->whereIn('status', ['completed', 'sold', 'paired'])
                       ->whereNotNull('start_date')
                       ->where('start_date', '!=', '');
            })
            ->orWhere(function($subQuery) {
                // OR show matured referral bonuses that have been paired with buyers
                $subQuery->where('get_from', 'refferal-bonus')
                       ->where('status', 'completed')
                       ->whereNotNull('matured_at') // Only matured bonuses
                       ->where('is_ready_to_sell', 1) // Ready to sell
                       ->whereHas('pairedWithThis'); // Only paired bonuses
            });
        })
        ->get();
            
        $totalSoldShares = $allSoldShares->count();
        // Count matured shares: those that are ready to sell OR fully sold
        $maturedShares = $allSoldShares->filter(function($share) {
            return $share->is_ready_to_sell == 1 || $share->status === 'sold';
        })->count();
        // Count running shares: those not ready to sell and not fully sold
        $runningShares = $allSoldShares->filter(function($share) {
            return $share->is_ready_to_sell == 0 && $share->status !== 'sold';
        })->count();
        $totalInvestment = $allSoldShares->sum('share_will_get');
        $totalEarnings = $allSoldShares->sum('profit_share');
        $totalReturn = $totalInvestment + $totalEarnings;

        // Get all referral bonuses (automatically floated to market)
        $availableReferralBonuses = UserShare::with(['trade', 'invoice.reff_user', 'pairedWithThis'])
            ->where('user_id', \auth()->user()->id)
            ->where('get_from', 'refferal-bonus')
            ->where('status', 'completed')
            ->where('is_ready_to_sell', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate bought and sold share statistics for current filter
        $boughtShares = $allSoldShares->where('get_from', 'purchase')->count();
        $soldSharesCount = $allSoldShares->where('get_from', '!=', 'purchase')->count();
        
        return view('user-panel.sold-shares', compact(
            'pageTitle', 
            'soldShares', 
            'totalSoldShares', 
            'maturedShares', 
            'runningShares', 
            'totalInvestment', 
            'totalEarnings', 
            'totalReturn',
            'availableReferralBonuses',
            'boughtShares',
            'soldSharesCount'
        ));
    }
    public function support()
    {
        $pageTitle = __('translation.support');

        return view('user-panel.support', compact('pageTitle'));
    }

    public function supportNew()
    {
        $pageTitle = __('translation.support');
        $supportFormEnabled = get_gs_value('support_form_enabled', false) ?? 1;

        return view('user-panel.support-new', compact('pageTitle', 'supportFormEnabled'));
    }

    public function howItWorksPage()
    {
        $pageTitle = 'How it works';

        $policy = Policy::where('slug', 'how-it-work')->first();

        return view('user-panel.how-it-works', compact('pageTitle', 'policy'));
    }
    public function privacyPolicy()
    {
        $policy = Policy::where('slug', 'privacy-policy')->first();
        $pageTitle = $policy ? $policy->title : 'Privacy Policy';

        return view('user-panel.privacy-policy', compact('pageTitle', 'policy'));
    }
    public function termsAndConditions()
    {
        $policy = Policy::where('slug', 'terms-and-conditions')->first();
        $pageTitle = $policy ? $policy->title : 'Terms and Conditions';

        return view('user-panel.terms-conditions', compact('pageTitle', 'policy'));
    }
    public function confidentialityPolicy()
    {
        $policy = Policy::where('slug', 'confidentiality-policy')->first();
        $pageTitle = $policy ? $policy->title : 'Confidentiality Policy';

        return view('user-panel.confidentiality-policy', compact('pageTitle', 'policy'));
    }

    /**
     * Get live statistics data for dashboard
     */
    public function getLiveStatistics(Request $request)
    {
        try {
            $type = $request->get('type', 'all');
            
            switch ($type) {
            case 'leaderboard':
                $data = $this->getLeaderboardData();
                return response()->json([
                    'leaderboard' => $data,
                    'last_updated' => now()->format('Y-m-d H:i:s')
                ]);
                
            case 'realtime':
                $data = $this->getRealtimeStatsData();
                return response()->json([
                    'activities' => $data,
                    'last_updated' => now()->format('Y-m-d H:i:s')
                ]);
                
            case 'referrers':
                $data = $this->getTopReferrersData();
                return response()->json([
                    'referrers' => $data,
                    'last_updated' => now()->format('Y-m-d H:i:s')
                ]);
                
            default:
                return response()->json([
                    'leaderboard' => $this->getLeaderboardData(),
                    'realtime_stats' => $this->getRealtimeStatsData(),
                    'top_referrers' => $this->getTopReferrersData(),
                    'last_updated' => now()->format('Y-m-d H:i:s')
                ]);
        }
        } catch (\Exception $e) {
            \Log::error('Live Statistics API Error: ' . $e->getMessage(), [
                'type' => $request->get('type', 'all'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Unable to load live statistics',
                'message' => 'Please try again later',
                'leaderboard' => [],
                'realtime_stats' => [],
                'top_referrers' => [],
                'last_updated' => now()->format('Y-m-d H:i:s')
            ], 500);
        }
    }

    /**
     * Get top traders leaderboard - only users with completed shares
     */
    private function getLeaderboardData()
    {
        return User::select('users.username', 'users.name')
            ->selectRaw('COALESCE(SUM(user_shares.amount + COALESCE(user_shares.profit_share, 0)), 0) as total_investment_profit')
            ->selectRaw('COALESCE(SUM(user_shares.amount + COALESCE(user_shares.profit_share, 0)), 0) as total_investment')
            ->join('user_shares', function($join) {
                $join->on('users.id', '=', 'user_shares.user_id')
                     ->whereIn('user_shares.status', ['completed', 'paired', 'running']); // Include active shares
            })
            ->where('users.role_id', 2) // Only regular users
            ->whereIn('users.status', ['active']) // Fix: Use correct status values from database
            ->groupBy('users.id', 'users.username', 'users.name')
            ->having('total_investment_profit', '>', 0)
            ->orderBy('total_investment_profit', 'DESC')
            ->limit(10)
            ->get();
    }

    /**
     * Get real-time user activities (bought and sold shares)
     */
    private function getRealtimeStatsData()
    {
        // Recent bought shares (last 24 hours) - show individual purchase amounts
        $recentBought = UserShare::select('user_shares.id', 'user_shares.ticket_no', 'user_shares.amount', 'user_shares.created_at')
            ->selectRaw('users.username, trades.name as trade_name')
            ->join('users', 'user_shares.user_id', '=', 'users.id')
            ->join('trades', 'user_shares.trade_id', '=', 'trades.id')
            ->where('user_shares.get_from', 'purchase') // Only show actual purchases
            ->where('user_shares.created_at', '>=', now()->subDay())
            ->orderBy('user_shares.created_at', 'DESC')
            ->limit(15)
            ->get()
            ->map(function($share) {
                return [
                    'id' => $share->id,
                    'username' => $share->username,
                    'trade_name' => $share->trade_name,
                    'ticket_no' => $share->ticket_no,
                    'amount' => $share->amount,
                    'type' => 'bought',
                    'time' => $share->created_at->diffForHumans(),
                    'created_at' => $share->created_at
                ];
            });

        // Recent sold transactions (last 24 hours) - show individual transaction amounts from user_share_pairs
        $recentSold = \DB::table('user_share_pairs')
            ->select(
                'user_share_pairs.id',
                'user_share_pairs.share as transaction_amount',
                'user_share_pairs.created_at',
                'seller_users.username as seller_username',
                'buyer_users.username as buyer_username', 
                'seller_shares.ticket_no',
                'trades.name as trade_name'
            )
            ->join('user_shares as seller_shares', 'user_share_pairs.paired_user_share_id', '=', 'seller_shares.id')
            ->join('user_shares as buyer_shares', 'user_share_pairs.user_share_id', '=', 'buyer_shares.id')
            ->join('users as seller_users', 'seller_shares.user_id', '=', 'seller_users.id')
            ->join('users as buyer_users', 'buyer_shares.user_id', '=', 'buyer_users.id')
            ->join('trades', 'seller_shares.trade_id', '=', 'trades.id')
            ->where('user_share_pairs.created_at', '>=', now()->subDay())
            ->orderBy('user_share_pairs.created_at', 'DESC')
            ->limit(15)
            ->get()
            ->map(function($pair) {
                return [
                    'id' => $pair->id,
                    'username' => $pair->seller_username, // Show seller's username
                    'buyer_username' => $pair->buyer_username, // Also track buyer for context
                    'trade_name' => $pair->trade_name,
                    'ticket_no' => $pair->ticket_no,
                    'amount' => $pair->transaction_amount, // Show exact transaction amount (e.g., 6000 not 12000)
                    'type' => 'sold',
                    'time' => \Carbon\Carbon::parse($pair->created_at)->diffForHumans(),
                    'created_at' => \Carbon\Carbon::parse($pair->created_at)
                ];
            });

        // Merge and sort by time, limit to exactly 10 items
        $allActivities = $recentBought->concat($recentSold)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        return $allActivities;
    }

    /**
     * Get top 10 referrers by referral count
     * Only shows active users and counts only their active referrals
     */
    private function getTopReferrersData()
    {
        try {
            // Use a subquery approach for better reliability and proper filtering
            $referrers = User::select('users.id', 'users.username', 'users.name')
                ->selectRaw('(SELECT COUNT(*) FROM users as ref WHERE ref.refferal_code = users.username AND ref.id != users.id AND ref.role_id = 2 AND ref.status = "active") as referral_count')
                ->where('users.role_id', 2) // Only regular users can be referrers
                ->where('users.status', 'active') // Exclude suspended/blocked users from referrers list
                ->havingRaw('referral_count > 0') // Only users with active referrals
                ->orderBy('referral_count', 'DESC')
                ->limit(10)
                ->get();

            \Log::info('Top Referrers Query Result:', [
                'count' => $referrers->count(),
                'data' => $referrers->toArray()
            ]);

            return $referrers;
        } catch (\Exception $e) {
            \Log::error('Error in getTopReferrersData:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty collection on error
            return collect([]);
        }
    }
}
