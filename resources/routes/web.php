<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\GeneralSettingController;
use App\Http\Controllers\Admin\PolicyController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\AllocateShareController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OthersController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\TradePeriodController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserShareController;
use App\Http\Controllers\UserSharePaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
//Language Translation
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);
Route::get('cache-clear', function(){
    \Artisan::call('optimize');
    \Artisan::call('cache:clear');
    return 'success';
});


Route::group(['middleware'=>['auth', 'if_user_blocked']],function () {

    Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');

    Route::prefix('admin')->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('admin.index');

        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('admin.role.index');
            Route::post('/', [RoleController::class, 'store'])->name('admin.role.store');
            Route::patch('/{role_id}', [RoleController::class, 'update'])->name('admin.role.update');
            Route::get('/delete/{role_id}', [RoleController::class, 'destroy'])->name('admin.role.delete');
            Route::get('/permission/{role_id}', [RoleController::class, 'permission'])->name('admin.role.permission');

            Route::patch('/permission/{role_id}',[RoleController::class, 'updatePermission'])->name('admin.role.permission.save');

        });
        Route::controller(StaffController::class)->prefix('staff')->group(function () {
            Route::get('/', 'index')->name('admin.staff.index');
            Route::get('/create', 'create')->name('admin.staff.create');
            Route::post('/', 'store')->name('admin.staff.store');
            Route::get('/{id}/edit', 'edit')->name('admin.staff.edit');
            Route::patch('/{id}', 'update')->name('admin.staff.update');
            Route::get('/delete/{id}', 'destroy')->name('admin.staff.delete');
        });

        Route::get('policy/{slug}', [PolicyController::class, 'edit'])->name('policy.edit');
        Route::patch('policy/{id}', [PolicyController::class, 'update'])->name('policy.update');

        Route::get('announcement', [AnnouncementController::class, 'index'])->name('announcement.index');
        Route::get('announcement/create', [AnnouncementController::class, 'createAnnouncement'])->name('announcement.create');
        Route::post('announcement', [AnnouncementController::class, 'store'])->name('announcement.store');
        Route::get('announcement/{id}/edit', [AnnouncementController::class, 'edit'])->name('announcement.edit');
        Route::patch('announcement/{id}', [AnnouncementController::class, 'update'])->name('announcement.update');
        Route::get('announcement/delete/{id}', [AnnouncementController::class, 'destroy'])->name('announcement.delete');


        Route::controller(TradeController::class)->prefix('trade')->group(function () {
            Route::get('/', 'index')->name('admin.trade.index');
            Route::get('/create', 'create')->name('admin.trade.create');
            Route::post('/', 'store')->name('admin.trade.store');
            Route::get('/{id}/edit', 'edit')->name('admin.trade.edit');
            Route::patch('/{id}', 'update')->name('admin.trade.update');
            Route::get('/delete/{id}', 'destroy')->name('admin.trade.delete');
        });

        Route::controller(TradePeriodController::class)->prefix('trade/period')->group(function () {
            Route::get('/', 'index')->name('admin.period.index');
            Route::get('/create', 'create')->name('admin.period.create');
            Route::post('/', 'store')->name('admin.period.store');
            Route::get('/{id}/edit', 'edit')->name('admin.period.edit');
            Route::patch('/{id}', 'update')->name('admin.period.update');
            Route::get('/delete/{id}', 'destroy')->name('admin.period.destroy');
        });



        Route::post('update/bought-share-status', [UserShareController::class, 'updateShareStatusAsFailed'])->name('share.status.updateAsFailed');
        Route::post('update/sold-share-status', [UserShareController::class, 'updateAsReadyToSell'])->name('share.status.updateAsReadyToSell');

        Route::post('share/payment', [UserSharePaymentController::class, 'payment'])->name('share.payment');
        Route::post('share/payment/approve', [UserSharePaymentController::class, 'paymentApprove'])->name('share.paymentApprove');

        Route::get('email', [AnnouncementController::class, 'createEmail'])->name('email.create');
        Route::post('email/send', [AnnouncementController::class, 'sendEmail'])->name('email.send');
        Route::get('support', [SupportController::class, 'supportsForAdmin'])->name('admin.support');

        Route::get('set/min-max-trading-amount', [GeneralSettingController::class, 'updateTradingPrice'])->name('admin.updateTradingPrice');
        Route::post('set/min-max-trading-amount', [GeneralSettingController::class, 'saveTradingPrice'])->name('admin.saveTradingPrice');

        Route::get('set/text-rate', [GeneralSettingController::class, 'setTaxRate'])->name('admin.setTaxRate');
        Route::post('set/text-rate', [GeneralSettingController::class, 'saveTaxRate'])->name('admin.saveTaxRate');


        Route::get('allocate/share/history', [AllocateShareController::class, 'allocateShareHistory'])->name('admin.allocate.share.history');
        Route::get('allocate/share/remove/{share_id}', [AllocateShareController::class, 'destroy'])->name('admin.allocate.share.destroy');
        Route::get('allocate/share', [AllocateShareController::class, 'allocateShare'])->name('admin.allocate.share');
        Route::post('allocate/share/save', [AllocateShareController::class, 'saveAllocateShare'])->name('admin.allocate.saveAllocateShare');
        Route::post('get/share/by/trade', [AllocateShareController::class, 'getShareByTradeAndUser'])->name('admin.getShareByTradeAndUser');
        Route::get('transfer/share', [AllocateShareController::class, 'transferShare'])->name('admin.transfer.share');
        Route::post('transfer/share/save', [AllocateShareController::class, 'saveTransferShare'])->name('admin.allocate.saveTransferShare');

        Route::post('user/status/update/{user_id}', [UserController::class, 'statusUpdate'])->name('user.status.update');


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

//    Route::get('{any}', [HomeController::class, 'index'])->name('index');


    Route::resource('supports', SupportController::class);




    //Update User Details
    Route::patch('/update-profile/{id}', [HomeController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/update-password/{id}', [HomeController::class, 'updatePassword'])->name('updatePassword');

    Route::get('dashboard', [App\Http\Controllers\User\HomeController::class, 'index'])->name('user.dashboard');

    Route::post('bid', [OthersController::class, 'bid'])->name('user.bid');


    Route::get('users/{slug}', [App\Http\Controllers\UserController::class, 'index'])->name('users.status');
    Route::get('user/{user_id}', [UserController::class, 'show'])->name('user.single');
    Route::post('users/{id}/status-update', [App\Http\Controllers\UserController::class, 'statusUpdate'])->name('users.status-update');
    Route::resource('users', App\Http\Controllers\UserController::class);

    Route::get('admin/setting/sms', [SettingController::class, 'createSmsSetting'])->name('admin.setting.sms.create');
    Route::get('admin/setting/mail', [SettingController::class, 'createMailSetting'])->name('admin.setting.mail.create');

    Route::get('notification/read/{id}', [OthersController::class, 'notification_read'])->name('notification.read');

});







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





