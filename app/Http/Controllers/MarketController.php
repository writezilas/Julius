<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Rules\ValidateTimeRange;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MarketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $markets = Market::OrderBy('open_time')->get();

        $pageTitle = 'Markets';
        
        return view('admin-panel.markets.index', compact('pageTitle', 'markets'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTitle = 'Create Market';
        
        return view('admin-panel.markets.form', compact('pageTitle'));
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
            'open_time' => [
                'required',
                'date_format:H:i',
                'before:close_time',
                new ValidateTimeRange,
            ],
            'close_time' => [
                'required',
                'date_format:H:i',
                'after:open_time',
            ],
        ]);

        if(Market::create($data)) {
            toastr()->success('Market has been created successfully');
        }else {
            toastr()->error('Failed to create market');
        }

        return redirect()->route('admin.markets.index');

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Market  $market
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pageTitle = 'Edit Market';
        $market = Market::findOrFail($id);

        return view('admin-panel.markets.form', compact('pageTitle', 'market'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Market  $market
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'open_time' => [
                'required',
                'date_format:H:i',
                'before:close_time',
                new ValidateTimeRange($id),
            ],
            'close_time' => [
                'required',
                'date_format:H:i',
                'after:open_time',
            ],
        ]);


        $market = Market::findOrFail($id);

        if($market->update($data)) {
            toastr()->success('Market has been created successfully');
        }else {
            toastr()->error('Failed to create market');
        }

        return redirect()->route('admin.markets.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Market  $market
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $market = Market::findOrFail($id);

        if($market->delete()) {
            toastr()->success('Market has been deleted successfully');
        }else {
            toastr()->error('Failed to delete market');
        }

        return back();
    }
}
