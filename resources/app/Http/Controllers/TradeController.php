<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $trades = Trade::OrderBy('id', 'desc')->get();

        $pageTitle = 'Trades';

        return view('admin-panel.trades.index', compact('pageTitle', 'trades'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTitle = 'Create Trade';
        return view('admin-panel.trades.create', compact('pageTitle'));
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
            'name' => 'bail|required',
            'price' => 'bail|required',
        ]);

        $data['slug'] = Str::slug($request->name, '-');

        if(Trade::create($data)) {
            toastr()->success('Trade has been created successfully');
        }else {
            toastr()->error('Failed to create trade');
        }

        return back();

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Trade  $trade
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pageTitle = 'Edit Trade';
        $trade = Trade::findOrFail($id);

        return view('admin-panel.trades.edit', compact('pageTitle', 'trade'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Trade  $trade
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'bail|required',
            'price' => 'bail|required',
        ]);

        $trade = Trade::findOrFail($id);

        $data['slug'] = Str::slug($request->name, '-');


        if($request->status) {
            $data['status'] = 1;
        }else {
            $data['status'] = 0;
        }

        if($trade->update($data)) {
            toastr()->success('Trade has been created successfully');
        }else {
            toastr()->error('Failed to create trade');
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Trade  $trade
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $trade = Trade::findOrFail($id);

        if($trade->delete()) {
            toastr()->success('Trade has been deleted successfully');
        }else {
            toastr()->error('Failed to delete trade');
        }

        return back();
    }
}
