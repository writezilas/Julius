@extends('layouts.master')
@php($pageTitle = __('translation.soldshares') . ' Info')
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
                        <table id="alternative-pagination" class="table align-middle table-hover table-bordered" style="width:100%">
                            <thead>
                            <tr>
                                <th>SR No.</th>
                                <th>Ticket no</th>
                                <th>Share type</th>
                                <th>Start date</th>
                                <th>Investment</th>
                                <th>Earning</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Time remaining</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($soldShares as $share)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{ $share->ticket_no }}</td>
                                    <td>{{ $share->trade->name }}</td>
                                    <td>{{ $share->start_date }}</td>
                                    <td>{{ $share->share_will_get }}</td>
                                    <td>{{ $share->profit_share }}</td>
                                    <td>{{ $share->share_will_get + $share->profit_share }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ getSoldShareStatus($share) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($share->is_ready_to_sell == 1)
                                            <p>Shared matured</p>
                                        @else
                                            <p id="sold-share-timer{{ $share->id }}"></p>
                                        @endif

                                    </td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            {{--                                            <a href="#" class="btn btn-primary">Payers</a>--}}
                                            <a href="{{ route('sold-share.view', $share->id) }}" class="btn btn-info">Details</a>
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
        @foreach($soldShares as $singleShare)
            // Set the date we're counting down to
            @if($singleShare->is_ready_to_sell == 0)
                getSoldShareCounterTime('{{ \Carbon\Carbon::parse($singleShare->start_date)->addDays($singleShare->period) }}', "sold-share-timer"+{{ $singleShare->id }}, {{ $singleShare->id }})
            @endif
        @endforeach

        function getSoldShareCounterTime(startTime, id, shareId) {
            var countDownDate = new Date(startTime + ' UTC').getTime();

            // Update the count down every 1 second
            var x = setInterval(function() {

                // Get today's date and time
                var now = new Date().getTime();

                // Find the distance between now and the count down date
                var distance = countDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Output the result in an element with id="demo"
                document.getElementById(id).innerHTML = days + "d " + hours + "h "
                    + minutes + "m " + seconds + "s ";

                // If the count down is over, write some text
                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById(id).innerHTML = "Share matured";

                //$.post('{{ route('share.status.updateAsReadyToSell') }}', {_token:'{{ csrf_token() }}', id: shareId});
                }
            }, 1000);
        }

    </script>
@endsection
