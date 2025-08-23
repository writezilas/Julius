<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\GeneralSettingController;
use App\Http\Controllers\Admin\PolicyController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\AllocateShareController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\OthersController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\TradePeriodController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserShareController;
use App\Http\Controllers\UserSharePaymentController;
use App\Http\Controllers\PermissionController;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

// Account suspended route (outside auth middleware since user can't login)
Route::get('/account/suspended', [App\Http\Controllers\Auth\LoginController::class, 'suspended'])->name('account.suspended');
// Account blocked route (outside auth middleware since user can't login)
Route::get('/account/blocked', [App\Http\Controllers\Auth\LoginController::class, 'blocked'])->name('account.blocked');

// Suspension status check (requires authentication)
Route::middleware('auth')->get('/suspension/status', [App\Http\Controllers\Auth\SuspensionController::class, 'checkStatus'])->name('suspension.status');
Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

 
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
 
    return redirect('/');
})->middleware(['auth', 'signed'])->name('verification.verify'); 
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    toastr()->success('Email verification link sent successfully');
    return back();
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
//Language Translation
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);
Route::get('cache-clear', function () {
    \Artisan::call('optimize');
    \Artisan::call('cache:clear');
    return 'success';
});


Route::group(['middleware' => ['auth', 'if_user_blocked', 'checkSessionExpiration', 'verified']], function () {

    Route::get('/', [App\Http\Controllers\User\HomeController::class, 'root'])->name('root');

    Route::prefix('admin')->group(function () {
        Route::get('permission-sync', [PermissionController::class, 'update']);
        Route::get('/', [HomeController::class, 'index'])->name('admin.index');

        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->middleware('permission:role-index')->name('admin.role.index');
            Route::post('/', [RoleController::class, 'store'])->middleware('permission:role-create')->name('admin.role.store');
            Route::patch('/{role_id}', [RoleController::class, 'update'])->middleware('permission:role-update')->name('admin.role.update');
            Route::get('/delete/{role_id}', [RoleController::class, 'destroy'])->middleware('permission:role-delete')->name('admin.role.delete');
            Route::get('/permission/{role_id}', [RoleController::class, 'permission'])->middleware('permission:permission-edit')->name('admin.role.permission');

            Route::patch('/permission/{role_id}', [RoleController::class, 'updatePermission'])->middleware('permission:permission-edit')->name('admin.role.permission.save');
        });
        Route::controller(StaffController::class)->prefix('staff')->group(function () {
            Route::get('/', 'index')->name('admin.staff.index')->middleware('permission:staff-index');
            Route::get('/create', 'create')->name('admin.staff.create')->middleware('permission:staff-create');
            Route::post('/', 'store')->name('admin.staff.store')->middleware('permission:staff-create');
            Route::get('/{id}/edit', 'edit')->name('admin.staff.edit')->middleware('permission:staff-edit');
            Route::patch('/{id}', 'update')->name('admin.staff.update')->middleware('permission:staff-update');
            Route::get('/delete/{id}', 'destroy')->name('admin.staff.delete')->middleware('permission:staff-delete');
        });

        Route::get('policy/{slug}', [PolicyController::class, 'edit'])->name('policy.edit');
        Route::patch('policy/{id}', [PolicyController::class, 'update'])->name('policy.update');

        Route::get('announcement', [AnnouncementController::class, 'index'])->name('announcement.index')->middleware('permission:announcement-index');
        Route::get('announcement/create', [AnnouncementController::class, 'createAnnouncement'])->name('announcement.create')->middleware('permission:announcement-create');
        Route::post('announcement', [AnnouncementController::class, 'store'])->name('announcement.store')->middleware('permission:announcement-create');
        Route::get('announcement/{id}/edit', [AnnouncementController::class, 'edit'])->name('announcement.edit')->middleware('permission:announcement-edit');
        Route::patch('announcement/{id}', [AnnouncementController::class, 'update'])->name('announcement.update')->middleware('permission:announcement-update');
        Route::get('announcement/delete/{id}', [AnnouncementController::class, 'destroy'])->name('announcement.delete')->middleware('permission:announcement-delete');


        Route::controller(TradeController::class)->prefix('trade')->group(function () {
            Route::get('/', 'index')->name('admin.trade.index')->middleware('permission:trade-index');
            Route::get('/create', 'create')->name('admin.trade.create')->middleware('permission:trade-create');
            Route::post('/', 'store')->name('admin.trade.store')->middleware('permission:trade-create');
            Route::get('/{id}/edit', 'edit')->name('admin.trade.edit')->middleware('permission:trade-edit');
            Route::patch('/{id}', 'update')->name('admin.trade.update')->middleware('permission:trade-update');
            Route::get('/delete/{id}', 'destroy')->name('admin.trade.delete')->middleware('permission:trade-delete');
        });
        Route::controller(MarketController::class)->prefix('market')->name('admin.markets.')->group(function () {
            Route::get('/', 'index')->name('index')->middleware('permission:market-index');
            Route::get('/create', 'create')->name('create')->middleware('permission:market-create');
            Route::post('/', 'store')->name('store')->middleware('permission:market-create');
            Route::get('/{id}/edit', 'edit')->name('edit')->middleware('permission:market-edit');
            Route::put('/{id}', 'update')->name('update')->middleware('permission:market-update');
            Route::get('/delete/{id}', 'destroy')->name('delete')->middleware('permission:trade-delete');
        });

        Route::controller(TradePeriodController::class)->prefix('trade/period')->group(function () {
            Route::get('/', 'index')->name('admin.period.index')->middleware('permission:trade-periods-index');
            Route::get('/create', 'create')->name('admin.period.create')->middleware('permission:trade-periods-create');
            Route::post('/', 'store')->name('admin.period.store')->middleware('permission:trade-periods-create');
            Route::get('/{id}/edit', 'edit')->name('admin.period.edit')->middleware('permission:trade-periods-edit');
            Route::patch('/{id}', 'update')->name('admin.period.update')->middleware('permission:trade-periods-update');
            Route::get('/delete/{id}', 'destroy')->name('admin.period.destroy')->middleware('permission:trade-periods-delete');
        });



        Route::post('update/bought-share-status', [UserShareController::class, 'updateShareStatusAsFailed'])->name('share.status.updateAsFailed');
        Route::post('update/sold-share-status', [UserShareController::class, 'updateAsReadyToSell'])->name('share.status.updateAsReadyToSell');

        Route::post('share/payment', [UserSharePaymentController::class, 'payment'])->name('share.payment');
        Route::get('shares/payment', [UserSharePaymentController::class, 'sharesPayment'])->name('shares.payment');
        Route::post('share/payment/approve', [UserSharePaymentController::class, 'paymentApprove'])->name('share.paymentApprove');

        Route::get('email', [AnnouncementController::class, 'createEmail'])->name('email.create')->middleware('permission:send-email');
        Route::post('email/send', [AnnouncementController::class, 'sendEmail'])->name('email.send')->middleware('permission:send-email');
        Route::get('sms', [AnnouncementController::class, 'createSms'])->name('sms.create')->middleware('permission:send-sms');
        Route::post('sms/send', [AnnouncementController::class, 'sendSms'])->name('sms.send')->middleware('permission:send-sms');
        Route::get('support', [SupportController::class, 'supportsForAdmin'])->name('admin.support')->middleware('permission:support-index');

        Route::get('set/min-max-trading-amount', [GeneralSettingController::class, 'updateTradingPrice'])->name('admin.updateTradingPrice')->middleware('permission:set-min-max-trading-amount-view');
        Route::post('set/min-max-trading-amount', [GeneralSettingController::class, 'saveTradingPrice'])->name('admin.saveTradingPrice')->middleware('permission:set-min-max-trading-amount-update');

        Route::get('set/text-rate', [GeneralSettingController::class, 'setTaxRate'])->name('admin.setTaxRate')->middleware('permission:set-income-tax-rate-view');
        Route::post('set/text-rate', [GeneralSettingController::class, 'saveTaxRate'])->name('admin.saveTaxRate')->middleware('permission:set-income-tax-rate-update');


        Route::get('allocate/share/history', [AllocateShareController::class, 'allocateShareHistory'])->name('admin.allocate.share.history')->middleware('permission:allocate-share-to-user-history');
        Route::get('allocate/share/remove/{share_id}', [AllocateShareController::class, 'destroy'])->name('admin.allocate.share.destroy')->middleware('permission:allocate-share-to-user-history-delete');
        Route::get('allocate/share', [AllocateShareController::class, 'allocateShare'])->name('admin.allocate.share')->middleware('permission:allocate-share-to-user');
        Route::post('allocate/share/save', [AllocateShareController::class, 'saveAllocateShare'])->name('admin.allocate.saveAllocateShare')->middleware('permission:allocate-share-to-user');
        Route::post('get/share/by/trade', [AllocateShareController::class, 'getShareByTradeAndUser'])->name('admin.getShareByTradeAndUser');
        Route::get('transfer/share', [AllocateShareController::class, 'transferShare'])->name('admin.transfer.share')->middleware('permission:transfer-share-from-user');
        Route::post('transfer/share/save', [AllocateShareController::class, 'saveTransferShare'])->name('admin.allocate.saveTransferShare')->middleware('permission:transfer-share-from-user');

        Route::post('user/status/update/{user_id}', [UserController::class, 'statusUpdate'])->name('user.status.update')->middleware('permission:customer-update');
        Route::get('/permission-denied', [PermissionController::class, 'denied'])->name('permission.denied');

        Route::get('setting/sms', [SettingController::class, 'createSmsSetting'])->name('admin.setting.sms.create')->middleware('permission:sms-api-page-view');
        Route::post('setting/sms', [SettingController::class, 'storeSmsSetting'])->name('admin.setting.sms.store');
        Route::get('setting/mail', [SettingController::class, 'createMailSetting'])->name('admin.setting.mail.create')->middleware('permission:email-api-page-view');
        Route::post('setting/mail', [SettingController::class, 'storeMailSetting'])->name('admin.setting.email.store')->middleware('permission:email-api-page-update');
        Route::get('general-setting', [SettingController::class, 'generalSetting'])->name('admin.general-setting')->middleware('permission:general-setting-view');
        Route::post('general-setting', [SettingController::class, 'generalSettingStore'])->name('admin.general-setting')->middleware('permission:general-setting-update');

        Route::get('users/{slug}', [App\Http\Controllers\UserController::class, 'index'])->name('users.status')->middleware('permission:customer-index');
        Route::get('user/{user_id}', [UserController::class, 'show'])->name('user.single')->middleware('permission:customer-view');
        Route::put('user/{id}', [UserController::class, 'update'])->name('admin.user.update')->middleware('permission:customer-update');
        Route::post('users/{id}/status-update', [App\Http\Controllers\UserController::class, 'statusUpdate'])->name('users.status-update')->middleware('permission:customer-update');
        Route::resource('users', App\Http\Controllers\UserController::class);

        // Chat Settings Routes
        Route::controller(App\Http\Controllers\Admin\ChatSettingController::class)->prefix('chat-settings')->group(function () {
            Route::get('/', 'index')->name('admin.chat-settings.index');
            Route::put('/', 'update')->name('admin.chat-settings.update');
            Route::post('/toggle', 'toggleChat')->name('admin.chat-settings.toggle');
            Route::get('/settings', 'getSettings')->name('admin.chat-settings.get');
            Route::post('/reset', 'resetToDefault')->name('admin.chat-settings.reset');
        });
    });

    Route::get('profile', [HomeController::class, 'profile'])->name('profile');

    Route::get('sold-shares', [HomeController::class, 'soldShares'])->name('users.sold_shares');
    Route::get('/sold-shares/view/{id}', [UserShareController::class, 'soldShareView'])->name('sold-share.view');

    Route::get('bought-shares', [HomeController::class, 'boughtShares'])->name('users.bought_shares');
    Route::get('/bought-shares/view/{id}', [UserShareController::class, 'boughtShareView'])->name('bought-share.view');

    Route::get('referrals', [HomeController::class, 'referrals'])->name('users.referrals');
    Route::get('support', [HomeController::class, 'support'])->name('users.support');
    Route::get('/how-it-works', [HomeController::class, 'howItWorksPage'])->name('page.how_it_work');
    Route::get('/privacy-policy', [HomeController::class, 'privacyPolicy'])->name('page.privacy_policy');
    Route::get('/terms-and-conditions', [HomeController::class, 'termsAndConditions'])->name('page.termsAndConditions');
    Route::get('/confidentiality-policy', [HomeController::class, 'confidentialityPolicy'])->name('page.confidentialityPolicy');

    // Chat System Routes
    Route::prefix('chat')->group(function () {
        Route::get('/', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
        Route::get('/conversations', [App\Http\Controllers\ChatController::class, 'getConversations'])->name('chat.conversations');
        Route::get('/conversations/{id}/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
        Route::post('/conversations/{id}/messages', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
        Route::post('/conversations/{id}/read', [App\Http\Controllers\ChatController::class, 'markAsRead'])->name('chat.read');
        Route::get('/unread-count', [App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chat.unread_count');
        Route::get('/settings', [App\Http\Controllers\ChatController::class, 'getChatSettings'])->name('chat.settings');
    });

    //    Route::get('{any}', [HomeController::class, 'index'])->name('index');

    Route::resource('supports', SupportController::class);

    //Update User Details
    Route::patch('/update-profile/{id}', [HomeController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-password/{id}', [HomeController::class, 'updatePassword'])->name('updatePassword');

    Route::get('dashboard', [App\Http\Controllers\User\HomeController::class, 'root'])->name('user.dashboard');

    Route::post('bid', [OthersController::class, 'bid'])->name('user.bid');


    Route::get('notification/read/{id}', [OthersController::class, 'notification_read'])->name('notification.read');

    // Live Statistics API
    Route::get('api/live-statistics', [HomeController::class, 'getLiveStatistics'])->name('api.live-statistics');
    Route::get('notification/read-all', [OthersController::class, 'notification_readAll'])->name('notification.read-all');
    Route::get('change-mode', [UserController::class, 'changeMode'])->name('changeMode');
});

Route::get('revert-suspend-users', [UserController::class, 'revertSuspendUsers'])->name('revertSuspendUsers');
Route::get('cron-for-every-update', [CronController::class, 'cronForEveryUpdate'])->name('cronForEveryUpdate');
Route::get('truncate-tables', [SettingController::class, 'trancateTables'])->name('trancateTables');
// initial artisan command
Route::get('storage/link', function () {
    Artisan::call('storage:link');
    return 'Storage linked';
});


Route::get('clear/cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    return 'cache cleared';
});

// Specific route for signup to avoid the catch-all route
Route::get('signup', function () {
    return redirect()->route('register');
});

// Catch-all route moved to the end to avoid conflicts
Route::get('{slug}', function ($slug) {
    // Only allow specific slugs to prevent conflicts with existing routes
    if (in_array($slug, ['about', 'contact', 'faq', 'help', 'info'])) {
        return view($slug);
    }
    abort(404);
});
