{{-- @php
$openTime = get_gs_value('open_market');
$closeTime = get_gs_value('close_market');
$todayDate = now()->format('Y-m-d');
$now = \Carbon\Carbon::now();
$open = \Carbon\Carbon::parse($todayDate.' '.$openTime);
$close = \Carbon\Carbon::parse($todayDate.' '.$closeTime);
$isTradeOpen = 0;
if($now->between($close, $open)){
    $isTradeOpen = 1;
}

// if market is closed, get the next open time
if($now->gt($close)){
    $open = \Carbon\Carbon::parse($todayDate.' '.$openTime)->addDay();
    $close = \Carbon\Carbon::parse($todayDate.' '.$closeTime)->addDay();
}
@endphp --}}
@php
    $appTimezone = get_app_timezone();
    $now = \Carbon\Carbon::now($appTimezone);
    $todayDate = $now->format('Y-m-d');
    
    // Use the new helper functions for timezone-aware market timing
    $isTradeOpen = is_market_open();
    $timeSlots = get_markets();
    
    // Get next opening time if market is closed
    $open = null;
    if (!$isTradeOpen) {
        $open = get_next_market_open_time();
    }
@endphp
@if($isTradeOpen || count($timeSlots) == 0)
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
@else
<div class="col-xl-6">
    <div class="card card-height-100">
        <div class="card-header align-items-center d-flex">
            <h4 class="card-title mb-0 flex-grow-1">Market Closed</h4>
        </div><!-- end card header -->

        <div class="card-body">
            <div class="table-card p-4">
                <p>Auction closed at the moment. Click REFRESH when it is time to bid.</p>
                @if($open)
                    <p class="text-muted">Market will open at: <strong>{{ $open->format('h:i A') }}</strong></p>
                    <p class="text-muted small">Time shown in {{ $appTimezone }} timezone</p>
                    <span id="count-down" data-time="{{$open->utc()}}">0d 0h 0m 0s</span>
                @endif
                <div class="mt-3">
                    <a href="{{ route('user.dashboard') }}" class="btn btn-primary">Refresh</a>
                </div>
            </div>
        </div> <!-- .card-body-->
    </div> <!-- .card-->
</div>
@section('script')
<script>
    function getCounterTime(startTime, id) {
        // Parse the input date string into a UTC date object
        var countDownDate = new Date(startTime + ' UTC').getTime();
        // Update the count down every 1 second
        var x = setInterval(function() {
            // Get the current UTC date and time
            var now = new Date().getTime();

            // Find the distance between now and the count down date
            var distance = countDownDate - now;

            // Time calculations for days, hours, minutes, and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Output the result in an element with id="demo"
            document.getElementById(id).innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";

            // If the count down is over, write some text
            if (distance < 0) {
                // plus one day                
                clearInterval(x);
            }
        }, 1000);
    }
    @if($open)
        getCounterTime('{{ $open->utc() }}', "count-down");
    @endif
</script>

@endsection
@endif