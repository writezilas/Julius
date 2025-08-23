<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\TradePeriod;
use Illuminate\Http\Request;

class TradePeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pageTitle = 'Trade periods';
        $periods = TradePeriod::orderBy('id', 'desc')->get();
        return view('admin-panel.trades.period.index', compact('pageTitle', 'periods'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTitle = 'Create trade';

        return view('admin-panel.trades.period.create', compact('pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'days' => 'bail|required',
            'percentage' => 'bail|required',
        ]);

        if($request->status) {
            $data['status'] = 1;
        }else {
            $data['status'] = 0;
        }

        if(TradePeriod::create($data)) {
            toastr()->success('Period has been created successfully');
        }else {
            toastr()->error('Failed to create period');
        }

        return back();


    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TradePeriod  $tradePeriod
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pageTitle = 'Edit period';
        $period = TradePeriod::findOrFail($id);

        return view('admin-panel.trades.period.edit', compact('pageTitle', 'period'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TradePeriod  $tradePeriod
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $period = TradePeriod::findOrFail($id);
        $data = $request->validate([
            'days' => 'bail|required',
            'percentage' => 'bail|required',
        ]);

        if($request->status) {
            $data['status'] = 1;
        }else {
            $data['status'] = 0;
        }

        if($period->update($data)) {
            toastr()->success('Period has been updated successfully');
        }else {
            toastr()->error('Failed to update period');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TradePeriod  $tradePeriod
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $period = TradePeriod::findOrFail($id);

        if($period->delete()) {
            toastr()->success('Period has been deleted successfully');
        }else {
            toastr()->error('Failed to delete period');
        }

        return back();
    }
}
