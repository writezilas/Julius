<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\DB;

class GeneralSettingController extends Controller
{
    public function index()
    {
        return view('admin-panel.setting.index');
    }
    public function updateTradingPrice()
    {
        $pageTitle = 'Update min & max trading price';

        $minTrading = GeneralSetting::where('key', 'min_trading_price')->value('value');
        $maxTrading = GeneralSetting::where('key', 'max_trading_price')->value('value');

        return view('admin-panel.settings.trading-price', compact('pageTitle', 'minTrading', 'maxTrading'));
    }

    public function saveTradingPrice(Request $request)
    {
        $request->validate([
            'min_trading_price' => 'required|numeric',
            'max_trading_price' => 'required|numeric'
        ]);

        GeneralSetting::updateOrCreate(
            ['key' => 'min_trading_price'],
            ['value' => $request->min_trading_price]
        );
        GeneralSetting::updateOrCreate(
            ['key' => 'max_trading_price'],
            ['value' => $request->max_trading_price]
        );

        toastr()->success('Minimum and maximum trading price has been updated successfully');
        return back();

    }

    public function setTaxRate()
    {
        $pageTitle = 'Set tax rate';

        $taxRate = GeneralSetting::where('key', 'tax_rate')->value('value');

        return view('admin-panel.settings.tax', compact('pageTitle', 'taxRate'));
    }

    public function saveTaxRate(Request $request)
    {
        $request->validate([
            'tax_rate' => 'required|numeric',
        ]);

        GeneralSetting::updateOrCreate(
            ['key' => 'tax_rate'],
            ['value' => $request->tax_rate]
        );

        toastr()->success('Tax rate has been updated successfully');
        return back();

    }

    public function supportFormSettings()
    {
        $pageTitle = 'Support Form Settings';

        $supportFormEnabled = GeneralSetting::where('key', 'support_form_enabled')->value('value') ?? 1;

        return view('admin-panel.settings.support-form', compact('pageTitle', 'supportFormEnabled'));
    }

    public function saveSupportFormSettings(Request $request)
    {
        $request->validate([
            'support_form_enabled' => 'required|boolean',
        ]);

        GeneralSetting::updateOrCreate(
            ['key' => 'support_form_enabled'],
            ['value' => $request->support_form_enabled]
        );

        $status = $request->support_form_enabled ? 'enabled' : 'disabled';
        toastr()->success("Support form has been {$status} successfully");
        return back();
    }

}
