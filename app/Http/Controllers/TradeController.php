<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\Response;

class TradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Trade::with(['userShares'])->withCount('userShares');

        // Apply filters based on request parameters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        if ($request->filled('quantity_min')) {
            $query->where('quantity', '>=', $request->quantity_min);
        }

        if ($request->filled('quantity_max')) {
            $query->where('quantity', '<=', $request->quantity_max);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $validSorts = ['id', 'name', 'price', 'buying_price', 'quantity', 'status', 'created_at'];
        if (in_array($sortBy, $validSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Get paginated results
        $trades = $query->paginate(15)->withQueryString();

        // Get statistics for dashboard cards
        $stats = [
            'total_trades' => Trade::count(),
            'active_trades' => Trade::where('status', 1)->count(),
            'inactive_trades' => Trade::where('status', 0)->count(),
            'total_user_shares' => \App\Models\UserShare::count(),
        ];

        $pageTitle = 'Trades Management';

        return view('admin-panel.trades.index', compact('pageTitle', 'trades', 'stats'));
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

    /**
     * Export trades data to CSV
     *
     * @param Request $request
     * @return Response
     */
    public function export(Request $request)
    {
        $query = Trade::with(['userShares']);

        // Apply same filters as index method
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        if ($request->filled('quantity_min')) {
            $query->where('quantity', '>=', $request->quantity_min);
        }

        if ($request->filled('quantity_max')) {
            $query->where('quantity', '<=', $request->quantity_max);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $validSorts = ['id', 'name', 'price', 'buying_price', 'quantity', 'status', 'created_at'];
        if (in_array($sortBy, $validSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $trades = $query->get();

        // Create CSV content
        $csvContent = "ID,Name,Slug,Price (KSH),Buying Price (KSH),Quantity,Status,User Shares Count,Created At\n";
        
        foreach ($trades as $trade) {
            $status = $trade->status == 1 ? 'Active' : 'Inactive';
            $userSharesCount = $trade->userShares->count();
            
            $csvContent .= sprintf(
                "%d,\"%s\",\"%s\",%.2f,%.2f,%d,\"%s\",%d,\"%s\"\n",
                $trade->id,
                addcslashes($trade->name, '"'),
                addcslashes($trade->slug, '"'),
                $trade->price,
                $trade->buying_price,
                $trade->quantity,
                $status,
                $userSharesCount,
                $trade->created_at->format('Y-m-d H:i:s')
            );
        }

        $filename = 'trades_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }
}
