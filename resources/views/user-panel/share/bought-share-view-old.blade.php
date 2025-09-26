@extends('layouts.master')
@section('title') {{$pageTitle}} @endsection
@section('css')

@endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') @lang('translation.dashboard') @endslot
@slot('title') {{$pageTitle}} @endslot
@endcomponent

<div class="row">
    @if($share->get_from === 'purchase')
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
                                <th>Seller name</th>
                                <th>Seller username</th>
                                <th>Seller M-pesa name</th>
                                <th>Seller M-pesa number</th>
                                <th>Paired share quantity</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($share->pairedShares as $key => $pairedShare)
                
                                @php
                                $payment = \App\Models\UserSharePayment::where('user_share_pair_id', $pairedShare->id)->orderBy('id', 'desc')->exists();
                                @endphp
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $pairedShare->pairedShare->user->name }}</td>
                                <td>{{ $pairedShare->pairedShare->user->username }}</td>
                                <td>{{ json_decode($pairedShare->pairedShare->user->business_profile)->mpesa_name }}</td>
                                <td>{{ json_decode($pairedShare->pairedShare->user->business_profile)->mpesa_no }}</td>
                                <td>{{ $pairedShare->share }}</td>
                                <td>
                                    @if($pairedShare->is_paid == 1)
                                    <span class="badge bg-success">Paid and confirmed</span>
                                    @elseif($payment)
                                    <span class="badge bg-info">Paid, waiting for confirmation</span>
                                    @elseif(\Carbon\Carbon::parse($pairedShare->created_at)->addHour(3) >= now() && $pairedShare->is_paid == 0)
                                    <span class="badge bg-primary">Waiting for payment</span>
                                    @else
                                    <span class="badge bg-danger">Payment time expired</span>
                                    @endif
                                </td>
                                <td>
                                    @if($pairedShare->is_paid == 1)
                                    <span class="badge bg-success">Paid and confirmed</span>
                                    @elseif($payment)
                                    <span class="badge bg-info">Paid, waiting for confirmation</span>
                                    @elseif(\Carbon\Carbon::parse($pairedShare->created_at)->addHour(3) >= now() && $pairedShare->is_paid == 0)
                                    <div class="btn-group" role="group" aria-label="Basic example"></a>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $pairedShare->id }}">
                                            Pay now
                                        </button>
                                    </div>
                                    @else
                                    <span class="badge bg-danger">Payment time expired</span>
                                    @endif
                                </td>
                            </tr>

                            <!-- Modal -->
                            <div class="modal fade" id="paymentModal{{ $pairedShare->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Payment submit form</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('share.payment') }}" method="post">
                                            @csrf
                                            <div class="modal-body">

                                                <div class="payment-details mb-4">
                                                    <h5>
                                                        You are buying <b>{{ $pairedShare->share }} shares </b> from the MR/MS <b>{{ $pairedShare->pairedShare->user->name }}</b>.
                                                        Each share cost {{ formatPrice($pairedShare->userShare->trade->price) }}</b>.
                                                    </h5>
                                                    <h5>So you have to pay {{ $pairedShare->share }} X {{ $pairedShare->userShare->trade->price }} = {{ formatPrice($pairedShare->share * $pairedShare->userShare->trade->price) }}</h5>
                                                    <h5>
                                                        <b>
                                                            <i>Please pay the amount in this <q>{{ json_decode($pairedShare->pairedShare->user->business_profile)->mpesa_no }}</q> and submit the form.</i>
                                                        </b>
                                                    </h5>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Your name</label>
                                                    <input type="text" class="form-control bg-light" name="name" value="{{ json_decode(auth()->user()->business_profile)->mpesa_name ?? '--' }}" readonly>
                                                    @error('name')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>

                                                <input type="hidden" value="{{ $pairedShare->user_share_id }}" name="user_share_id">
                                                <input type="hidden" value="{{ $pairedShare->id }}" name="user_share_pair_id">
                                                <input type="hidden" value="{{ $pairedShare->pairedShare->user->id }}" name="receiver_id">
                                                <input type="hidden" value="{{ auth()->user()->id }}" name="sender_id">
                                                <input type="hidden" value="{{ json_decode($pairedShare->pairedShare->user->business_profile)->mpesa_no }}" name="received_phone_no">
                                                <div class="mb-3">
                                                    <label class="form-label">Phone no <small>(The number you sent the money from)</small></label>
                                                    <input type="text" class="form-control bg-light" name="number" value="{{ json_decode(auth()->user()->business_profile)->mpesa_no ?? '--' }}" readonly>
                                                    @error('number')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Payment transaction id</label>
                                                    <input type="text" class="form-control" name="txs_id" value="{{ old('txs_id') }}" required>
                                                    @error('txs_id')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Amount</label>
                                                    <input type="number" class="form-control bg-light" name="amount" value="{{ $pairedShare->share * $pairedShare->userShare->trade->price }}" readonly>
                                                    @error('amount')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Note</label>
                                                    <textarea class="form-control" name="note_by_sender">{{ old('note_by_sender') }}</textarea>
                                                    @error('note_by_sender')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment history</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>SI</th>
                                <th>Username</th>
                                <th>Seller M-pesa no</th>
                                <th>Sender phone no</th>
                                <th>Sent amount</th>
                                <th>status</th>
                                <th>Time</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($share->payments as $key => $payment)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $payment->receiver->username }}</td>
                                <td>{{ $payment->received_phone_no }}</td>
                                <td>{{ $payment->number }}</td>
                                <td>{{ $payment->amount }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $payment->status == 'conformed' ? 'Confirmed' : $payment->status }}
                                    </span>
                                </td>
                                <td>
                                    {{ $payment->created_at }}
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
    @else
        @if($share->invoice)
            <div class="card">
                <div class="card-body text-center py-5">
                    <h2 class="text-info">No History, As the share is {{ str_replace('-', ' ', $share->get_from) }} itself.</h2>
                    <h4 class="mt-3">If you want to learn more about it. Please contact us from
                        <a href="{{ route('users.support') }}" class="text-info text-decoration-underline">here</a>
                    </h4>
                    <a href="{{ route('users.bought_shares') }}" class="btn btn-primary mt-3">Return back</a>
                </div>
                <div class="card" id="demo">
                    <div class="row">
                        <!--end col-->
                        <div class="col-lg-12">
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-lg-3 col-6">
                                        <p class="text-muted mb-2 text-uppercase fw-semibold">Invoice No</p>
                                        <h5 class="fs-14 mb-0">#<span id="invoice-no">{{$share->ticket_no}}</span></h5>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-3 col-6">
                                        <p class="text-muted mb-2 text-uppercase fw-semibold">Date</p>
                                        <h5 class="fs-14 mb-0"><span id="invoice-date">{{\Carbon\Carbon::parse($share->created_at)->format('d M, Y')}}</span> <small class="text-muted" id="invoice-time">{{\Carbon\Carbon::parse($share->created_at)->format('H:ia')}}</small></h5>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-3 col-6">
                                        <p class="text-muted mb-2 text-uppercase fw-semibold">Payment Status</p>
                                        <span class="badge badge-soft-success fs-11" id="payment-status">Paid</span>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-3 col-6">
                                        <p class="text-muted mb-2 text-uppercase fw-semibold">Total Amount</p>
                                        <h5 class="fs-14 mb-0">Ksh<span id="total-amount">{{number_format($share->invoice->new_amount,2)}}</span></h5>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </div>
                            <!--end card-body-->
                        </div>
                        <!--end col-->

                        <!--end col-->
                        <div class="col-lg-12">
                            <div class="card-body p-4">
                                <div class="table-responsive">
                                    <table class="table table-borderless text-center table-nowrap align-middle mb-0">
                                        <thead>
                                            <tr class="table-active">
                                                <th scope="col" style="width: 50px;">#</th>
                                                <th scope="col">User</th>
                                                <th scope="col">Rate</th>
                                                {{-- <th scope="col" class="text-end">Amount</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody id="products-list">
                                            <tr>
                                                <th scope="row">01</th>
                                                <td class="text-center">
                                                    <span class="fw-medium">{{$share->invoice->referral ? $share->invoice->referral->name : '--'}}</span>

                                                </td>
                                                <td>Ksh{{number_format($share->invoice->add_amount,2)}}</td>

                                                {{-- <td class="text-end">Ksh{{number_format($share->invoice->old_amount,2)}}</td> --}}
                                            </tr>

                                        </tbody>
                                    </table>
                                    <!--end table-->
                                </div>
                                <div class="border-top border-top-dashed mt-2">
                                    <table class="table table-borderless table-nowrap align-middle mb-0 ms-auto" style="width:250px">
                                        <tbody>
                                            <tr>
                                                <td>Sub Total</td>
                                                <td class="text-end">Ksh{{number_format($share->invoice->old_amount,2)}}</td>
                                            </tr>
                                            <tr>
                                                <td>Add Amount</td>
                                                <td class="text-end">Ksh{{number_format($share->invoice->add_amount,2)}}</td>
                                            </tr>
                                            <tr class="border-top border-top-dashed fs-15">
                                                <th scope="row">Total Amount</th>
                                                <th class="text-end">Ksh{{number_format($share->invoice->new_amount,2)}}</th>
                                            </tr>
                                        </tbody>
                                    </table>
                                    </table>
                                    <!--end table-->
                                </div>

                                <div class="hstack gap-2 justify-content-end d-print-none mt-4">
                                    <a href="javascript:window.print()" class="btn btn-success"><i class="ri-printer-line align-bottom me-1"></i> Print</a>
                                </div>
                            </div>
                            <!--end card-body-->
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
            </div>
        @endif
    @endif
</div>

@endsection
@section('script')
@endsection
