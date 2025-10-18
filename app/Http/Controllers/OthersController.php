<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\GeneralSetting;
use App\Models\Log;
use App\Models\Trade;
use App\Models\UserShare;
use App\Models\UserSharePair;
use App\Models\UserSharePayment;
use App\Mail\NewSharePurchaseMail;
use App\Helpers\SettingHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OthersController extends Controller
{
    public function buySharePage($trade_id)
    {
        try {
            $trade = Trade::findOrFail($trade_id);
            
            // Check if trade is active
            if ($trade->status != '1') {
                toastr()->error('This trade is not currently available.');
                return redirect()->route('root');
            }
            
            // Get available shares for this trade
            $availableShares = checkAvailableSharePerTrade($trade->id);
            
            // Get min/max trading amounts
            $minTradeAmount = \App\Models\GeneralSetting::where('key', 'min_trading_price')->first();
            $maxTradeAmount = \App\Models\GeneralSetting::where('key', 'max_trading_price')->first();
            
            // Get active trade periods from admin settings
            $periods = \App\Models\TradePeriod::where('status', 1)->orderBy('days', 'asc')->get();
            
            $data = [
                'trade' => $trade,
                'availableShares' => $availableShares,
                'minAmount' => $minTradeAmount->value ?? 0,
                'maxAmount' => $maxTradeAmount->value ?? 1000000,
                'periods' => $periods,
            ];
            
            return view('user-panel.buy-share', $data);
            
        } catch (\Exception $e) {
            \Log::error('Buy share page error: ' . $e->getMessage());
            toastr()->error('Trade not found or unavailable.');
            return redirect()->route('root');
        }
    }

    public function bid(Request $request)
    {
        try {
            $data = $request->validate([
                'trade_id' => 'bail|required|integer|exists:trades,id',
                'amount' => 'bail|required|numeric|min:1',
                'period' => 'bail|required|integer|min:1',
            ]);

            $user = auth()->user();
            
            if (!$user) {
                toastr()->error('User authentication failed. Please login again.');
                return redirect()->route('login');
            }

            $trade = Trade::findOrFail($request->trade_id);
            
            // Validate trade is active
            if ($trade->status != '1') {
                toastr()->error('This trade is not currently available.');
                return back();
            }
            
            // Get the price from either price or amount field
            $tradePrice = $trade->price ?? $trade->amount ?? null;
            if (!$tradePrice || $tradePrice <= 0) {
                \Log::error('Trade has invalid price. Trade ID: ' . $trade->id . ', Price: ' . $tradePrice);
                toastr()->error('Trade pricing is not configured properly. Please contact support.');
                return back();
            }

            $minTradeAmount = GeneralSetting::where('key', 'min_trading_price')->first();
            $maxTradeAmount = GeneralSetting::where('key', 'max_trading_price')->first();

            if ($request->amount < ($minTradeAmount->value ?? 0)) {
                toastr()->info('Minimum trading amount is ' . ($minTradeAmount->value ?? 0));
                return back();
            }

            if ($request->amount > ($maxTradeAmount->value ?? 1000000)) {
                toastr()->info('Maximum trading amount is ' . ($maxTradeAmount->value ?? 1000000));
                return back();
            }

            // Calculate shares that will be purchased
            $sharesWillGet = floor($request->amount / $tradePrice);
            if ($sharesWillGet <= 0) {
                toastr()->error('Amount is not enough to buy at least one share. Please try with a higher amount.');
                return back();
            }

            // Use database transaction for data consistency
            DB::beginTransaction();
            
            try {
                // First, get shares without locking to check availability
                $shareCount = UserShare::whereTradeId($trade->id)
                    ->where('status', 'completed')
                    ->where('is_ready_to_sell', 1)
                    ->where('total_share_count', '>', 0)
                    ->where('user_id', '!=', auth()->user()->id)
                    ->whereHas('user', function ($query) {
                        $query->whereIn('status', ['active']);
                    })
                    ->sum('total_share_count');
                
                if ($sharesWillGet > $shareCount) {
                    DB::rollBack();
                    toastr()->error('Not enough shares available. Only ' . number_format($shareCount) . ' shares available. Try with a lower amount.');
                    return back();
                }
                
                if ($shareCount <= 0) {
                    DB::rollBack();
                    toastr()->error('No shares available for purchase at the moment. Please try again later.');
                    return back();
                }

                // Generate unique ticket number with better collision handling
                $ticketNo = 'AB-' . time() . rand(1000, 9999);
                $count = 1;
                while (UserShare::where('ticket_no', $ticketNo)->exists()) {
                    $ticketNo = 'AB-' . time() . rand(1000, 9999) . $count;
                    $count++;
                    if ($count > 10) { // Prevent infinite loop
                        throw new \Exception('Unable to generate unique ticket number');
                    }
                }

                $data['ticket_no'] = $ticketNo;
                $data['user_id'] = auth()->user()->id;
                $data['share_will_get'] = $sharesWillGet;
                $data['status'] = 'pending'; // Start with pending status
                $data['payment_deadline_minutes'] = get_gs_value('bought_time') ?? 60;
                
                // Create the buyer's share record first
                $createdShare = UserShare::create($data);
                
                // Validate the created share before proceeding
                if (!$createdShare || !$createdShare->id) {
                    throw new \Exception('Failed to create buyer share record');
                }
                
                // Log the successful creation for debugging
                \Log::info('Buyer share created successfully', [
                    'share_id' => $createdShare->id,
                    'user_id' => $createdShare->user_id,
                    'status' => $createdShare->status,
                    'amount' => $createdShare->amount
                ]);

                // Now get the shares with minimal locking for pairing
                $userShares = UserShare::whereTradeId($trade->id)
                    ->where('status', 'completed')
                    ->where('is_ready_to_sell', 1)
                    ->where('total_share_count', '>', 0)
                    ->where('user_id', '!=', auth()->user()->id)
                    ->whereHas('user', function ($query) {
                        $query->whereIn('status', ['active']);
                    })
                    ->orderBy('total_share_count', 'desc') // Order by count for optimal pairing
                    ->get();
                
                $totalShare = $sharesWillGet;
                $matchedShare = 0;
                $pairings = [];
                
                foreach ($userShares as $share) {
                    if ($matchedShare >= $sharesWillGet) {
                        break;
                    }
                    
                    // Only lock individual shares when we're about to modify them
                    $currentShare = UserShare::lockForUpdate()->find($share->id);
                    if (!$currentShare || $currentShare->total_share_count <= 0) {
                        continue; // Skip if share no longer exists or has no available shares
                    }
                    
                    $sharesToPair = min($totalShare, $currentShare->total_share_count);
                    
                    // Validate share status before pairing
                    if ($currentShare->status !== 'completed') {
                        \Log::warning('Share status changed during pairing. Share ID: ' . $currentShare->id . ', Status: ' . $currentShare->status);
                        continue;
                    }
                    
                    // Update share quantities atomically
                    $currentShare->increment('hold_quantity', $sharesToPair);
                    $currentShare->decrement('total_share_count', $sharesToPair);
                    $currentShare->save();
                    
                    // Store pairing info for batch creation
                    $pairings[] = [
                        'user_id' => $user->id,
                        'user_share_id' => $createdShare->id,
                        'paired_user_share_id' => $currentShare->id,
                        'share' => $sharesToPair,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    $matchedShare += $sharesToPair;
                    $totalShare -= $sharesToPair;
                    
                    if ($totalShare <= 0) {
                        break;
                    }
                }
                
                // Validate that we have enough matches
                if ($matchedShare < $sharesWillGet) {
                    throw new \Exception('Insufficient shares available for pairing. Matched: ' . $matchedShare . ', Required: ' . $sharesWillGet);
                }
                
                // Batch insert pairings for better performance
                if (!empty($pairings)) {
                    UserSharePair::insert($pairings);
                }
                
                // Update buyer share status to paired with start_date to satisfy constraint
                $createdShare->update([
                    'status' => 'paired',
                    'start_date' => now()->format('Y/m/d H:i:s')
                ]);
                
                // Save log
                $log = new Log();
                $log->remarks = "Share bought successfully.";
                $log->type = "share";
                $log->value = $sharesWillGet;
                $log->user_id = auth()->user()->id;
                $createdShare->logs()->save($log);
                
                // Store data for post-transaction operations
                $postTransactionData = [
                    'user' => $user,
                    'trade' => $trade,
                    'amount' => $request->amount,
                    'shares_will_get' => $sharesWillGet,
                    'ticket_no' => $ticketNo
                ];

                DB::commit();
                
                // Post-transaction operations (notifications and emails)
                // These operations run after the database transaction is committed for better performance
                $this->handlePostTransactionOperations($postTransactionData);
                
                // Clear cache for this trade since shares availability has changed
                $cacheService = new \App\Services\ShareAvailabilityCache();
                $cacheService->clearCache($trade->id);
                
                toastr()->success('Share bought successfully. Navigate to bought shares page and make payment');
                
                // Redirect to bought-shares page instead of going back
                return redirect()->route('users.bought_shares');
                
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                \Log::error('Database constraint violation in bid function - Code: ' . $e->getCode() . ' Message: ' . $e->getMessage());
                
                // Handle specific constraint violations with better error messages
                $errorMessage = $e->getMessage();
                
                if (str_contains($errorMessage, 'chk_user_share_status') || str_contains($errorMessage, 'chk_valid_status')) {
                    toastr()->error('Invalid share status. The share cannot be processed in its current state. Please try again later.');
                } elseif (str_contains($errorMessage, 'chk_paired_has_start_date')) {
                    toastr()->error('Share pairing validation failed. Please try again.');
                } elseif (str_contains($errorMessage, 'chk_quantities')) {
                    toastr()->error('Share quantity validation failed. Please try again.');
                } elseif (str_contains($errorMessage, 'foreign key constraint')) {
                    toastr()->error('Data reference error. Please contact support if this persists.');
                } elseif (str_contains($errorMessage, 'Duplicate entry') && str_contains($errorMessage, 'ticket_no')) {
                    toastr()->error('Ticket generation conflict. Please try again.');
                } elseif (str_contains($errorMessage, 'Invalid buyer share status') || 
                          str_contains($errorMessage, 'Invalid seller share status')) {
                    toastr()->error('Share pairing validation failed. Please try again.');
                } else {
                    // Generic database error
                    toastr()->error('A database error occurred. Please try again in a few moments.');
                }
                
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('General error in bid function - File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
                toastr()->error('Shares NOT Bought. Please try again or contact support if the issue persists.');
            }

            return back();
            
        } catch (\Exception $e) {
            \Log::error('Bid function validation error - File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            toastr()->error('An error occurred while processing your request. Please try again.');
            return back();
        }
    }


    public function notification_read($id)
    {
        try {
            $notification = auth()->user()->unreadNotifications->find($id);
            
            if (!$notification) {
                toastr()->error('Notification not found');
                return back();
            }
            
            $notification->read_at = now();
            $notification->save();
            
            if (isset($notification->data['for']) && $notification->data['for'] == 'payment-sent') {
                if (isset($notification->data['payment_id'])) {
                    $payment = UserSharePayment::find($notification->data['payment_id']);
                    return redirect()->route('sold-share.view', $payment->paired->paired_user_share_id);
                }
            } elseif (isset($notification->data['for']) && $notification->data['for'] == 'payment-received') {
                $payment = UserSharePayment::find($notification->data['payment_id']);
                return redirect()->route('bought-share.view', $payment->user_share_id);
            }
            
        } catch (\Exception $e) {
            \Log::error('Notification read error: ' . $e->getMessage());
            toastr()->error('Something went wrong. Please try again.');
        }
        
        return back();
    }
    
    public function notification_readAll()
    {
        try {
            $allNoti = auth()->user()->unreadNotifications;
            
            if ($allNoti->isEmpty()) {
                toastr()->info('No unread notifications found');
                return back();
            }
            
            foreach($allNoti as $noti){
                $noti->read_at = now();
                $noti->save();
            }
            
            toastr()->success('All notifications marked as read successfully');
            
        } catch (\Exception $e) {
            \Log::error('Mark all notifications as read error: ' . $e->getMessage());
            toastr()->error('Something went wrong. Please try again.');
        }
        
        return back();
    }
    
    /**
     * Handle post-transaction operations (notifications and emails)
     * This method runs outside the database transaction for better performance
     */
    protected function handlePostTransactionOperations($data)
    {
        // Create admin notification for new share purchase
        try {
            AdminNotification::newSharePurchase(
                $data['user'],
                $data['trade'],
                $data['amount'],
                $data['shares_will_get'],
                $data['ticket_no']
            );
            
            \Log::info("Admin notification created for share purchase: {$data['user']->username} - {$data['ticket_no']}");
        } catch (\Exception $e) {
            \Log::warning('Failed to create admin notification for share purchase: ' . $e->getMessage());
        }
        
        // Send admin email notification for new share purchase
        try {
            $adminEmail = SettingHelper::get('admin_email');
            if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($adminEmail)->send(new NewSharePurchaseMail(
                    $data['user'],
                    $data['trade'],
                    $data['amount'],
                    $data['shares_will_get'],
                    $data['ticket_no']
                ));
                \Log::info("Admin email sent successfully for share purchase: {$data['user']->username} - {$data['ticket_no']} to {$adminEmail}");
            } else {
                \Log::warning("Admin email not configured or invalid - skipping email notification for share purchase: {$data['user']->username} - {$data['ticket_no']}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send admin email for share purchase {$data['user']->username} - {$data['ticket_no']}: " . $e->getMessage());
        }
    }
}
