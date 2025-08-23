@extends('layouts.master')
@section('title') {{$pageTitle}} @endsection
@section('css')
    <style>
        .stats-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .share-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 15px;
            color: white;
        }
        .payment-detail-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            border-left: 4px solid #28a745;
        }
    </style>
@endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') @lang('translation.dashboard') @endslot
@slot('title') {{$pageTitle}} @endslot
@endcomponent

@php
    // Calculate statistics for purchased shares
    $totalSellers = $groupByShare->count();
    $totalAmount = 0;
    $paidCount = 0;
    $pendingCount = 0;
    $expiredCount = 0;
    
    foreach($groupByShare as $pairedShare) {
        $totalShare = $pairedShare->sum('pairedWithThis.share');
        $firstShare = $pairedShare->first();
        $tradePrice = $firstShare->trade->price;
        $totalAmount += $totalShare * $tradePrice;
        
        $pairedIds = $pairedShare->pluck('pairedWithThis.id');
        $payments = \App\Models\UserSharePayment::whereIn('user_share_pair_id', $pairedIds)->get();
        $pairedWithIsAllPaid = $pairedShare->where('pairedWithThis.is_paid', 0)->count();
        
        if($pairedWithIsAllPaid == 0 && count($payments)) {
            $paidCount++;
        } elseif(count($payments) > 0) {
            $pendingCount++;
        } elseif(\Carbon\Carbon::parse($pairedShare->first()->pairedWithThis->created_at)->addHour(3) >= now() && $pairedWithIsAllPaid != 0) {
            $pendingCount++;
        } else {
            $expiredCount++;
        }
    }
@endphp

<!-- Share Information Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card share-header">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h3 class="text-white mb-2">
                            <i class="ri-shopping-cart-line align-middle me-2"></i>
                            Bought Share: {{$share->ticket_no}}
                        </h3>
                        <p class="text-white-75 mb-0 fs-16">
                            Manage your purchases, track payments, and view seller information
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <div class="text-white">
                            <div class="display-6 mb-2">
                                <i class="ri-shopping-bag-line"></i>
                            </div>
                            <h6 class="text-white mb-0">Purchase Management</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Total Sellers</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success-subtle text-success rounded-2">
                                <i class="ri-user-line fs-16"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-2">{{$totalSellers}}</h4>
                        <span class="badge bg-success-subtle text-success"> 
                            <i class="ri-team-line align-middle"></i> Active 
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Total Amount</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-2">
                                <i class="ri-money-dollar-circle-line fs-16"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-2">Ksh {{number_format($totalAmount)}}</h4>
                        <span class="badge bg-primary-subtle text-primary"> 
                            <i class="ri-coins-line align-middle"></i> Purchase Value 
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Completed Payments</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-info-subtle text-info rounded-2">
                                <i class="ri-check-double-line fs-16"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-2">{{$paidCount}}</h4>
                        <span class="badge bg-info-subtle text-info"> 
                            <i class="ri-shield-check-line align-middle"></i> Confirmed 
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Pending</p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-warning-subtle text-warning rounded-2">
                                <i class="ri-time-line fs-16"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-2">{{$pendingCount}}</h4>
                        <span class="badge bg-warning-subtle text-warning"> 
                            <i class="ri-hourglass-line align-middle"></i> Awaiting 
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @if($share->get_from === 'purchase')
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title mb-0">
                            <i class="ri-shopping-cart-2-line align-middle me-2 text-success"></i>
                            Your Paired Shares
                        </h5>
                        <p class="text-muted mb-0">Manage your purchases and make payments to sellers</p>
                    </div>
                    <div class="col-auto">
                        <div class="d-flex flex-wrap align-items-center gap-1">
                            <button type="button" class="btn btn-soft-success btn-sm" data-bs-toggle="tooltip" 
                                    data-bs-placement="top" title="Refresh Data">
                                <i class="ri-refresh-line align-middle"></i>
                            </button>
                            <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-toggle="tooltip" 
                                    data-bs-placement="top" title="Export">
                                <i class="ri-download-line align-middle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                @if(count($groupByShare) > 0)
                    <div class="table-responsive">
                        <table id="pairedSharesTable" class="table table-hover align-middle table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center">#</th>
                                <th scope="col">
                                    <i class="ri-user-line align-middle me-1"></i>Seller Details
                                </th>
                                <th scope="col">
                                    <i class="ri-smartphone-line align-middle me-1"></i>MPESA Info
                                </th>
                                <th scope="col">
                                    <i class="ri-coins-line align-middle me-1"></i>Shares
                                </th>
                                <th scope="col" class="text-center">
                                    <i class="ri-list-check align-middle me-1"></i>Share Count
                                </th>
                                <th scope="col" class="text-center">
                                    <i class="ri-settings-line align-middle me-1"></i>Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $key = 1 @endphp
                            @foreach($groupByShare as $pairedShare)
                                @php
                                    $firstShare          = $pairedShare->first();
                                    $totalShare          = $pairedShare->sum('pairedWithThis.share');
                                    $user                = $firstShare->user;
                                    $businessProfile     = json_decode($user->business_profile);
                                    $pairedIds           = $pairedShare->pluck('pairedWithThis.id');
                                    $payments            = \App\Models\UserSharePayment::whereIn('user_share_pair_id', $pairedIds)->get();
                                    $pairedWithIsAllPaid = $pairedShare->where('pairedWithThis.is_paid', 0)->count();
                                    $tradePrice          = $firstShare->trade->price;
                                    $currentBusinessProfile = json_decode(auth()->user()->business_profile);
                                @endphp
                                {{-- @dd($pairedWithIsAllPaid) --}}
                            <tr>
                                <td class="text-center">
                                    <span class="fw-medium text-success">{{ $key }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-3">
                                            <div class="avatar-title bg-success-subtle text-success rounded-circle">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="fs-15 fw-semibold mb-0">{{ $user->name }}</h6>
                                            <p class="text-muted mb-0 fs-13">@{{ $user->username }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $businessProfile->mpesa_name ?? 'N/A' }}</span>
                                        <p class="text-muted mb-0 fs-13">{{ $businessProfile->mpesa_no ?? 'N/A' }}</p>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ri-coins-line text-success me-2"></i>
                                        <span class="fw-semibold fs-15">{{ number_format($totalShare) }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary px-3 py-2 cursor-pointer" 
                                          data-bs-toggle="modal" data-bs-target="#listModal{{ $user->id ."-". $firstShare->id }}">
                                        <i class="ri-eye-line align-middle me-1"></i>View ({{ $pairedShare->count() }})
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($pairedWithIsAllPaid == 0 && count($payments))
                                        <span class="badge bg-success-subtle text-success px-3 py-2">
                                            <i class="ri-check-double-line align-middle me-1"></i>Paid & Confirmed
                                        </span>
                                    @elseif(count($payments) > 0)
                                        <span class="badge bg-info-subtle text-info px-3 py-2">
                                            <i class="ri-time-line align-middle me-1"></i>Awaiting Confirmation
                                        </span>
                                    @elseif(\Carbon\Carbon::parse($firstShare->pairedWithThis->created_at)->addHour(3) >= now() && $pairedWithIsAllPaid != 0)
                                        <button type="button" class="btn btn-soft-success btn-sm" 
                                                data-bs-toggle="modal" data-bs-target="#paymentModal{{$user->id ."-". $firstShare->id }}">
                                            <i class="ri-secure-payment-line align-middle me-1"></i>Pay Now
                                        </button>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2">
                                            <i class="ri-close-circle-line align-middle me-1"></i>Payment Expired
                                        </span>
                                    @endif
                                </td>
                            </tr>


                            
                            <!--Payment Modal -->
                            <div class="modal fade" id="paymentModal{{$user->id ."-". $firstShare->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Payment submit form</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('shares.payment') }}" method="GET">
                                            
                                            <div class="modal-body">

                                                <div class="payment-details mb-4">
                                                    <h5>
                                                        You are buying <b>{{ $totalShare }} shares </b> from the MR/MS <b>{{ $user->name }}</b>.
                                                        Each share cost {{ formatPrice($tradePrice) }}</b>.
                                                    </h5>
                                                    <h5>So you have to pay {{ $totalShare }} X {{ $tradePrice }} = {{ formatPrice($totalShare * $tradePrice) }}</h5>
                                                    <h5>
                                                        <b>
                                                            <i>Please pay the amount in this <q>{{ $businessProfile->mpesa_no }}</q> and submit the form.</i>
                                                        </b>
                                                    </h5>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Your name</label>
                                                    <input type="text" class="form-control bg-light" name="name" value="{{ $currentBusinessProfile->mpesa_name ?? '--' }}" readonly>
                                                    @error('name')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>

                                                <input type="hidden" value="{{ $firstShare->pairedWithThis->user_share_id }}" name="user_share_id">
                                                @foreach($pairedIds as $pairedId)
                                                <input type="hidden" value="{{ $pairedId }}" name="user_share_pair_ids[]">
                                                @endforeach
                                                <input type="hidden" value="{{ $user->id }}" name="receiver_id">
                                                <input type="hidden" value="{{ auth()->user()->id }}" name="sender_id">
                                                <input type="hidden" value="{{ $businessProfile->mpesa_no }}" name="received_phone_no">
                                                <div class="mb-3">
                                                    <label class="form-label">Phone no <small>(The number you sent the money from)</small></label>
                                                    <input type="text" class="form-control bg-light" name="number" value="{{ $currentBusinessProfile->mpesa_no ?? '--' }}" readonly>
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
                                                    <input type="number" class="form-control bg-light" name="amount" value="{{ $totalShare * $tradePrice }}" readonly>
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
                            
                            {{-- listing model --}}
                            <div class="modal fade" id="listModal{{$user->id ."-". $firstShare->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">List of <b>{{$user->username}}<b> Shares</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="table-responsive">
                                            @include('components.share-pair-list')
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php $key++ @endphp
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
    <!-- DataTables for enhanced table functionality -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable if there are paired shares
            if ($('#pairedSharesTable').length) {
                $('#pairedSharesTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25], [5, 10, 25]],
                    language: {
                        search: "<i class='ri-search-line'></i>",
                        searchPlaceholder: "Search sellers...",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ sellers",
                        infoEmpty: "No sellers found",
                        infoFiltered: "(filtered from _MAX_ total sellers)"
                    },
                    columnDefs: [
                        { orderable: false, targets: [0, 4, 5] }, // Disable sorting on # Share Count and Action columns
                        { searchable: false, targets: [0, 4, 5] }  // Disable search on # Share Count and Action columns
                    ],
                    order: [[1, 'asc']] // Sort by seller name by default
                });
            }
            
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Animate statistics cards on load
            animateStatsCards();
        });

        // Animate statistics cards
        function animateStatsCards() {
            const cards = document.querySelectorAll('.stats-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(30px)';
                    card.style.transition = 'all 0.6s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 150);
            });
        }

        // Toast notification function
        function showToast(title, message, type = 'info') {
            const toastId = 'toast_' + Date.now();
            const iconClass = {
                'success': 'ri-check-line',
                'error': 'ri-close-line',
                'warning': 'ri-alert-line',
                'info': 'ri-information-line'
            }[type] || 'ri-information-line';
            
            const bgClass = {
                'success': 'bg-success',
                'error': 'bg-danger',
                'warning': 'bg-warning',
                'info': 'bg-primary'
            }[type] || 'bg-primary';

            const toastHtml = `
                <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="${iconClass} me-2"></i>${title} ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;

            // Create toast container if it doesn't exist
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }

            // Add toast to container
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            // Show toast
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 3000
            });
            toast.show();
            
            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', function () {
                toastElement.remove();
            });
        }

        // Refresh page data
        function refreshData() {
            showToast('Info', 'Refreshing data...', 'info');
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        // Export functionality (placeholder)
        function exportData() {
            showToast('Info', 'Export functionality coming soon', 'info');
        }

        // Add click events to action buttons
        document.addEventListener('DOMContentLoaded', function() {
            const refreshBtn = document.querySelector('[title="Refresh Data"]');
            const exportBtn = document.querySelector('[title="Export"]');
            
            if (refreshBtn) refreshBtn.addEventListener('click', refreshData);
            if (exportBtn) exportBtn.addEventListener('click', exportData);
            
            // Initialize animations when page loads
            setTimeout(animateStatsCards, 300);
        });
    </script>

    <!-- Enhanced responsive styles -->
    <style>
        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 1rem;
            }
            
            .share-header h3 {
                font-size: 1.5rem !important;
            }
            
            .share-header .display-6 {
                font-size: 2rem !important;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .avatar-xs {
                display: none;
            }
            
            .modal-lg {
                max-width: 95% !important;
            }
            
            .payment-details h5 {
                font-size: 1rem !important;
            }
        }
        
        @media (max-width: 576px) {
            .col-xl-3.col-md-6 {
                margin-bottom: 1rem;
            }
            
            .fs-22 {
                font-size: 1.2rem !important;
            }
            
            .btn-group {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-group .btn {
                border-radius: 0.375rem !important;
                margin-bottom: 0.25rem;
            }
        }
        
        /* Enhanced hover effects */
        .table tbody tr:hover {
            background-color: rgba(40, 167, 69, 0.05);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .badge {
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .cursor-pointer {
            cursor: pointer;
        }
        
        .cursor-pointer:hover {
            transform: scale(1.05);
            transition: all 0.2s ease;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .modal-content {
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .payment-detail-card {
            margin-bottom: 1.5rem;
        }
        
        .toast {
            min-width: 300px;
        }
        
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        
        .btn-soft-success:hover {
            background-color: #28a745 !important;
            color: white !important;
        }
        
        @else
            .table-responsive {
                overflow-x: auto;
            }
        @endif
    </style>
@endsection
