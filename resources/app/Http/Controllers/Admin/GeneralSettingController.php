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

}
