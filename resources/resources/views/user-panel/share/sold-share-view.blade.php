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
                    <h5 class="card-title mb-0">Your paired shares</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>SI</th>
                                    <th>Buyer name</th>
                                    <th>Buyer username</th>
                                    <th>MPESA name</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php
                                $pairedShares = \App\Models\UserSharePair::where('paired_user_share_id', $share->id)->orderBy('id', 'desc')->get();
                            @endphp

                            @foreach($pairedShares as $key => $pairedShare)
                                @php
                                    $payment = \App\Models\UserSharePayment::where('user_share_pair_id', $pairedShare->id)->orderBy('id', 'desc')->first();
                                @endphp
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $pairedShare->pairedUserShare->user->name }}</td>
                                    <td>{{ $pairedShare->pairedUserShare->user->username }}</td>
                                    <td>{{ json_decode($pairedShare->pairedUserShare->user->business_profile)->mpesa_name }}</td>
                                    <td>{{ $pairedShare->share }}</td>
                                    <td>
                                        @if($payment && $payment->status === 'paid')
                                            <span class="badge bg-success">Paid, waiting for confirmation</span>
                                        @elseif($payment && $payment->status === 'conformed')
                                            <span class="badge bg-success">Payment confirmed</span>
                                        @elseif(\Carbon\Carbon::parse($pairedShare->created_at)->addHour(3) >= now())
                                            <span class="badge bg-primary">Waiting for payment</span>
                                        @else
                                            <span class="badge bg-danger">Payment time expired</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Basic example"></a>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#soldShareDetails{{ $pairedShare->id }}">
                                                Details
                                            </button>
                                            {{-- FIXED: Always show modal, regardless of payment status --}}
                                            <div class="modal fade" id="soldShareDetails{{ $pairedShare->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="staticBackdropLabel">
                                                                @if($payment)
                                                                    Payment Confirmation
                                                                @else
                                                                    Waiting for Payment
                                                                @endif
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            @if($payment)
                                                                {{-- Payment exists - show payment details --}}
                                                                <table class="table table-bordered">
                                                                    <tbody>
                                                                        <tr>
                                                                            <th>Sender name</th>
                                                                            <td>{{ $payment->name }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Amount sent from</th>
                                                                            <td>{{ $payment->number }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Amount</th>
                                                                            <td>{{ formatPrice($payment->amount) }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Transaction no</th>
                                                                            <td>{{ $payment->txs_id }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Note by sender</th>
                                                                            <td>{{ $payment->note_by_sender }}</td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>

                                                                @if($payment->status === 'conformed')
                                                                    <div class="border border-dashed border-success p-3 my-3">
                                                                        <h3 class="text-center m-0 p-0">Payment completed. Thanks you</h3>
                                                                    </div>
                                                                @else
                                                                    <form id="paymentApproveForm{{$payment->id}}" action="{{ route('share.paymentApprove') }}" method="post">
                                                                        @csrf
                                                                        <div class="form-group">
                                                                            <label>Comment <small>if any</small></label>
                                                                            <textarea name="note_by_receiver" class="form-control"></textarea>
                                                                            <input type="hidden" value="{{ $payment->id }}" name="paymentId">
                                                                        </div>
                                                                        <button type="button" onclick="handlePaymentConformSubmit({{ $payment->id }})" class="btn btn-success mt-3 float-end">Confirm payment</button>
                                                                    </form>
                                                                @endif
                                                            @else
                                                                {{-- No payment yet - show waiting message --}}
                                                                <div class="alert alert-info">
                                                                    <h5><i class="fas fa-clock me-2"></i>Waiting for Payment</h5>
                                                                    <p>The buyer has not submitted payment for this pair yet.</p>
                                                                    <hr>
                                                                    <h6>Pair Details:</h6>
                                                                    <ul class="mb-0">
                                                                        <li><strong>Buyer:</strong> {{ $pairedShare->pairedUserShare->user->name }} ({{ $pairedShare->pairedUserShare->user->username }})</li>
                                                                        <li><strong>Share Amount:</strong> {{ number_format($pairedShare->share) }}</li>
                                                                        <li><strong>Paired Date:</strong> {{ $pairedShare->created_at->format('d M Y, H:i') }}</li>
                                                                        <li><strong>Payment Deadline:</strong> 
                                                                            @if(\Carbon\Carbon::parse($pairedShare->created_at)->addHours(3) >= now())
                                                                                <span class="text-success">{{ \Carbon\Carbon::parse($pairedShare->created_at)->addHours(3)->format('d M Y, H:i') }}</span>
                                                                            @else
                                                                                <span class="text-danger">Expired ({{ \Carbon\Carbon::parse($pairedShare->created_at)->addHours(3)->format('d M Y, H:i') }})</span>
                                                                            @endif
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal -->

                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script>
        function handlePaymentConformSubmit(paymentId) {
            $('#paymentApproveForm'+paymentId).submit();

            {{--$.post('{{ route(' ') }}', {_token:'{{ csrf_token() }}', paymentId: paymentId}, function (data) {--}}
            {{--    if(data == 'paymentConformSuccess') {--}}
            {{--        alert("Payment status update");--}}
            {{--    }else {--}}
            {{--        alert("Failed to update payment");--}}
            {{--    }--}}
            {{--    // location.reload();--}}
            {{--});--}}
        }
    </script>
@endsection
