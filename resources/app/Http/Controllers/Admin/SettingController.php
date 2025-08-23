<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function createSmsSetting() {
        $pageTitle = 'Sms setting';
        return view('admin-panel.settings.sms', compact('pageTitle'));
    }
    public function createMailSetting() {
        $pageTitle = 'Email setting';
        return view('admin-panel.settings.email', compact('pageTitle'));
    }
}
