# User Suspension System Setup Guide

This guide explains how to set up and configure the user suspension system for consecutive payment failures.

## Features Implemented

1. **Automatic Suspension**: Users are automatically suspended for 12 hours after 3 consecutive payment failures
2. **Timer Pause System**: Running share timers are paused during suspension and resumed when lifted  
3. **Comprehensive Tracking**: All payment failures and suspensions are logged and tracked
4. **Automatic Recovery**: Suspensions are automatically lifted after 12 hours
5. **Admin Management**: Console commands for managing suspensions and viewing statistics

## Files Created/Modified

### New Files Created:
- `app/Models/UserPaymentFailure.php` - Model for tracking payment failures
- `app/Services/PaymentFailureService.php` - Service for handling suspension logic
- `app/Http/Middleware/CheckSuspension.php` - Middleware to check suspension status
- `app/Http/Controllers/Auth/SuspensionController.php` - Controller for suspension page
- `app/Console/Commands/ManageSuspensions.php` - Command for managing suspensions
- `database/migrations/2025_08_24_000002_create_user_payment_failures_table.php` - Migration for payment failures table
- `database/migrations/2025_08_24_000003_add_timer_pause_fields_to_user_shares_table.php` - Migration for timer pause fields

### Files Modified:
- `app/Models/User.php` - Added suspension methods and relationships
- `app/Http/Helpers/helpers.php` - Updated payment failure handling with suspension logic
- `app/Http/Controllers/UserSharePaymentController.php` - Added payment failure reset on successful payments
- `resources/views/user-panel/sold-shares.blade.php` - Added paused timer indicators

## Setup Instructions

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Add Routes

Add these routes to your `routes/web.php` file:

```php
// Suspension routes
Route::middleware('auth')->group(function () {
    Route::get('/suspended', [App\Http\Controllers\Auth\SuspensionController::class, 'show'])->name('suspended');
    Route::get('/suspension/status', [App\Http\Controllers\Auth\SuspensionController::class, 'checkStatus'])->name('suspension.status');
});
```

### 3. Register Middleware

Add the CheckSuspension middleware to `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ... existing middleware
    'check.suspension' => \App\Http\Middleware\CheckSuspension::class,
];
```

### 4. Apply Middleware to Routes

Apply the suspension check middleware to your protected routes. In your `routes/web.php`, wrap your trading/user routes with the middleware:

```php
// Apply to all user panel routes
Route::middleware(['auth', 'verified', 'check.suspension'])->prefix('user')->name('user.')->group(function () {
    // Your existing user routes
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    Route::get('/shares', [UserShareController::class, 'index'])->name('shares.index');
    // ... other user routes
});
```

### 5. Schedule Console Command

Add the suspension management command to your `app/Console/Kernel.php` schedule:

```php
protected function schedule(Schedule $schedule)
{
    // Run every 5 minutes to check for expired suspensions
    $schedule->command('suspensions:manage --process-expired')->everyFiveMinutes();
    
    // Your existing scheduled commands...
    $schedule->command('paymentfailedshare:cron')->everyMinute();
}
```

### 6. Update IfUserBlocked Middleware (Optional)

Update the existing `app/Http/Middleware/IfUserBlocked.php` to also handle suspensions:

```php
public function handle(Request $request, Closure $next)
{
    if (auth()->check()) {
        $user = auth()->user();
        
        // Check suspension first
        if ($user->isSuspended()) {
            Auth::logout();
            toastr()->warning('Your account is suspended due to payment failures. Please try again later.');
            return redirect()->route('login');
        }
        
        // Existing block checks...
        if ($user->status == 'block' && $user->block_until) {
            // ... existing block logic
        }
    }

    return $next($request);
}
```

## Usage

### Console Commands

#### View Suspension Statistics
```bash
php artisan suspensions:manage --show-stats
```

#### Process Expired Suspensions
```bash
php artisan suspensions:manage --process-expired
```

#### Both (default)
```bash
php artisan suspensions:manage
```

### How It Works

1. **Payment Failure Detection**: When a share fails payment (3 hour timeout), the system tracks it
2. **Consecutive Failure Tracking**: Each user has a failure counter that increments on failures and resets on successful payments
3. **Automatic Suspension**: After 3 consecutive failures, the user is suspended for 12 hours
4. **Timer Pause**: All running share timers for the user are paused during suspension
5. **Automatic Recovery**: After 12 hours, the suspension is lifted and timers resume
6. **Prevention**: Suspended users cannot access trading features and are redirected to suspension page

### Testing the System

#### Test Scenario 1: Suspension Flow
1. Create test shares for a user
2. Let them fail payment (wait 3+ hours or manually set failed status)
3. Repeat 3 times consecutively
4. Verify user gets suspended for 12 hours
5. Verify their running shares are paused
6. Wait for or manually expire suspension
7. Verify user can access trading again and timers resume

#### Test Scenario 2: Reset Flow
1. Have a user with 2 consecutive failures
2. Make a successful payment
3. Verify failure count resets to 0
4. User should not be suspended

## Database Schema

### user_payment_failures table
- `id` - Primary key
- `user_id` - Foreign key to users
- `consecutive_failures` - Number of consecutive failures
- `last_failure_at` - Timestamp of last failure
- `suspended_at` - When user was suspended
- `suspension_lifted_at` - When suspension was lifted
- `failure_reason` - Reason for failure

### user_shares table (added fields)
- `timer_paused` - Boolean indicating if timer is paused
- `timer_paused_at` - When timer was paused
- `paused_duration_seconds` - Total seconds the timer was paused

### users table (existing fields used)
- `status` - User status (suspend, fine, block, etc.)
- `suspension_until` - When suspension expires

## Monitoring and Maintenance

1. **Daily Statistics**: Run `php artisan suspensions:manage --show-stats` to monitor system health
2. **Log Monitoring**: Check logs for suspension/resume activities
3. **Database Cleanup**: Consider periodic cleanup of old payment failure records
4. **Performance**: Index on `user_payment_failures.user_id` for better performance

## Troubleshooting

### Common Issues

1. **Middleware Not Working**: Ensure middleware is registered and applied to correct routes
2. **Timers Not Resuming**: Check if `paused_duration_seconds` is being calculated correctly
3. **Suspensions Not Lifting**: Ensure cron job is running and schedule is configured
4. **Route Conflicts**: Make sure suspension routes don't conflict with existing routes

### Debug Commands

```bash
# Check current suspensions
php artisan suspensions:manage --show-stats

# Check scheduled tasks
php artisan schedule:list

# Test suspension manually in tinker
php artisan tinker
>>> $user = User::find(1);
>>> $user->suspendForPaymentFailures();
>>> $user->liftSuspension();
```

## Customization Options

### Adjust Suspension Duration
Change the suspension duration in `User::suspendForPaymentFailures()`:
```php
$suspensionUntil = now()->addHours(24); // 24 hours instead of 12
```

### Change Failure Threshold
Modify the threshold in `UserPaymentFailure::shouldSuspend()`:
```php
return $this->consecutive_failures >= 5; // 5 failures instead of 3
```

### Custom Suspension Reasons
Add custom reasons when calling `handlePaymentFailure()`:
```php
$paymentFailureService->handlePaymentFailure($userId, 'Custom suspension reason');
```

This system provides comprehensive protection against payment defaults while ensuring legitimate users can resume trading after the suspension period.
