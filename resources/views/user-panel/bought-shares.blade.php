@extends('layouts.master')
@section('title') {{$pageTitle}}  @endsection
@section('css')

@endsection
@section('content')

    @component('components.breadcrumb')
        @slot('li_1') @lang('translation.dashboard') @endslot
        @slot('title') {{$pageTitle}} @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="alternative-pagination" class="table  align-middle table-hover table-bordered" style="width:100%">
                            <thead>
                            <tr>
                                <th>SR No.</th>
                                <th>Ticket no</th>
                                <th>Share type</th>
                                <th>Date</th>
                                <th>Share quantity</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Time remaining</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($boughtShares as $share)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{ $share->ticket_no }}</td>
                                    <td>{{ $share->trade->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($share->created_at)->format('d M Y') }}</td>
                                    <td>{{ $share->total_share_count }}</td>
                                    <td>{{ $share->amount }}</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            @if($share->payments && $share->status === 'failed' && $share->payments->count() > 0)
                                                @if(count($share->payments) == 1)
                                                    Paid, waiting for confirmation
                                                @else
                                                    Partially paid
                                                @endif  
                                            @else
                                                {{ $share->status }}
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        @if($share->status === 'failed' )
                                            
                                           Time Expired
                                        @elseif($share->status === 'completed' && $share->start_date != '')
                                            Payment completed
                                        @else
                                            <p id="timer{{ $share->id }}"></p>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                           <a href="{{ route('bought-share.view', $share->id) }}" class="btn btn-info">Details</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

@endsection
@section('script')

<script>

@foreach($boughtShares as $singleShare)
        @if($singleShare->status != 'failed')
            @php
                $boughtTimeMinutes = get_gs_value('bought_time') ?? 1;
                // Create expiry time in server timezone and convert to ISO format for JavaScript
                $expiryDateTime = \Carbon\Carbon::parse($singleShare->created_at)->addMinutes($boughtTimeMinutes);
                $expiryTimeISO = $expiryDateTime->toISOString(); // This will be in UTC
            @endphp

            getCounterTime('{{ $expiryTimeISO }}', "timer"+{{ $singleShare->id }}, {{ $singleShare->id }})
        @endif
    @endforeach

    function getCounterTime(startTime, id, shareId) {
    console.log('Expiry time (server timezone):', startTime);
    // Parse the date string assuming it's in the server's timezone (Africa/Nairobi)
    // Convert server time to local browser time for accurate countdown
    var countDownDate = new Date(startTime).getTime();
    
    console.log('Countdown target:', new Date(countDownDate));

    // Update the count down every 1 second
    var x = setInterval(function () {
        // Get the current local time
        var now = new Date().getTime();

        // Find the distance between now and the count down date
        var distance = countDownDate - now;

        // Time calculations for days, hours, minutes, and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Output the result in an element with id
        if (distance > 0) {
            document.getElementById(id).innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
        } else {
            // If the count down is over, write some text
            clearInterval(x);
            document.getElementById(id).innerHTML = "EXPIRED";

            // You can perform any actions here when the countdown expires
            // Example: Update status using jQuery's $.post
            //$.post('{{ route('share.status.updateAsFailed') }}', {_token: '{{ csrf_token() }}', id: shareId, status: 'failed'});
        }
    }, 1000);
}


</script>
@endsection
