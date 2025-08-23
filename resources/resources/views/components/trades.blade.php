<div class="row">
    @php
        $trades = \App\Models\Trade::where('status', 1)->OrderBy('id', 'desc')->get();
    @endphp
    @foreach($trades as $key => $trade)
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0 flex-grow-1">{{ $trade->name }}</h4>
                    <h6 class="mt-1 mb-0">{{ checkAvailableSharePerTrade($trade->id) }} Share available</h6>
                </div>
                <div class="card-body p-0">
                    <div class="p-3">
                        <form action="{{ route('user.bid') }}" method="post">
                            @csrf
                            <div>
                                <div class="input-group mb-3">
                                    <label class="input-group-text">Amount</label>
                                    <input type="number" class="form-control" placeholder="0000" name="amount">
                                    <input type="hidden" value="{{ $trade->id }}" name="trade_id">
                                </div>
                                <div class="input-group mb-0">
                                    <label class="input-group-text">Period</label>
                                    @php
                                        $periods = \App\Models\TradePeriod::where('status', 1)->orderBy('days', 'asc')->get();
                                    @endphp
                                    <select class="form-select" name="period">
                                        @foreach($periods as $period)
                                            <option value="{{ $period->days }}">{{$period->days}} days ({{$period->percentage}}%)</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3 pt-2">
                                <button type="submit" class="btn btn-primary w-100">Bid</button>
                            </div>
                        </form>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div>
    @endforeach
</div>
