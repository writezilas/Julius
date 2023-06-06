<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\HomeController;
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


Route::group(['middleware'=>'auth'],function () {

    Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');

    Route::prefix('admin')->group(function () {
        Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('admin.index');

        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('admin.role.index');
            Route::post('/', [RoleController::class, 'store'])->name('admin.role.store');
            Route::patch('/{role_id}', [RoleController::class, 'update'])->name('admin.role.update');
            Route::get('/delete/{role_id}', [RoleController::class, 'destroy'])->name('admin.role.delete');
            Route::get('/permission/{role_id}', [RoleController::class, 'permission'])->name('admin.role.permission');

            Route::patch('/permission/{role_id}',[RoleController::class, 'updatePermission'])->name('admin.role.permission.save');


            Route::controller(StaffController::class)->prefix('staff')->group(function () {
                Route::get('/', 'index')->name('admin.staff.index');
                Route::get('/create', 'create')->name('admin.staff.create');
                Route::post('/', 'store')->name('admin.staff.store');
                Route::get('/{id}/edit', 'edit')->name('admin.staff.edit');
                Route::patch('/{id}', 'update')->name('admin.staff.update');
                Route::get('/delete/{id}', 'destroy')->name('admin.staff.delete');
            });



        });
    });

    Route::get('profile', [HomeController::class, 'profile'])->name('profile');



});



//Update User Details
Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('updateProfile');
Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');

Route::get('dashboard', [App\Http\Controllers\User\HomeController::class, 'index'])->name('user.dashboard');


Route::get('users/{slug}', [App\Http\Controllers\UserController::class, 'index'])->name('users.status');
Route::post('users/{id}/status-update', [App\Http\Controllers\UserController::class, 'statusUpdate'])->name('users.status-update');
Route::resource('users', App\Http\Controllers\UserController::class);

Route::resource('supports', App\Http\Controllers\SupportController::class);




Route::get('admin/setting/sms', [SettingController::class, 'createSmsSetting'])->name('admin.setting.sms.create');
Route::get('admin/setting/mail', [SettingController::class, 'createMailSetting'])->name('admin.setting.mail.create');

Route::resource('announcement', AnnouncementController::class);

Route::get('{any}', [HomeController::class, 'index'])->name('index');






