@extends('layouts.master')
@section('title') {{$pageTitle}} @endsection
@section('css')
    <!-- Afresh Payment Form CSS -->
    <link href="{{ asset('assets/css/payment-form-afresh.css') }}?v={{ time() }}" rel="stylesheet" type="text/css" />
    
    <style>
        .stats-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            /* Disabled flickering transitions */
            transition: none !important;
            animation: none !important;
        }
        .stats-card:hover {
            /* Disabled flickering hover effects */
            transform: none !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
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
        // Skip if pairedShare collection is empty
        if ($pairedShare->isEmpty()) continue;
        
        $totalShare = $pairedShare->flatMap(function($share) {
            return $share->pairedWithThis ?? collect();
        })->sum('share');
        
        $firstShare = $pairedShare->first();
        if (!$firstShare || !$firstShare->trade) continue;
        
        $tradePrice = $firstShare->trade->price;
        $totalAmount += $totalShare * $tradePrice;
        
        $pairedIds = $pairedShare->flatMap(function($share) {
            return $share->pairedWithThis ? $share->pairedWithThis->pluck('id') : collect();
        });
        
        $payments = \App\Models\UserSharePayment::whereIn('user_share_pair_id', $pairedIds)->get();
        $pairedWithIsAllPaid = $pairedShare->flatMap(function($share) {
            return $share->pairedWithThis ? $share->pairedWithThis->where('is_paid', 0) : collect();
        })->count();
        
        if($pairedWithIsAllPaid == 0 && count($payments)) {
            $paidCount++;
        } elseif(count($payments) > 0) {
            $pendingCount++;
        } elseif($firstShare->pairedWithThis && $firstShare->pairedWithThis->isNotEmpty() && \Carbon\Carbon::parse($firstShare->pairedWithThis->first()->created_at)->addMinutes($share->payment_deadline_minutes ?? 60) >= now() && $pairedWithIsAllPaid != 0) {
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
                        <table id="pairedSharesTable" class="table align-middle table-nowrap mb-0">
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
                                    // Skip if no valid first share
                                    if (!$firstShare) continue;
                                    
                                    $totalShare          = $pairedShare->flatMap(function($share) {
                                        return $share->pairedWithThis ?? collect();
                                    })->sum('share');
                                    
                                    $user                = $firstShare->user;
                                    if (!$user) continue;
                                    
                                    $businessProfile     = json_decode($user->business_profile);
                                    $pairedIds           = $pairedShare->flatMap(function($share) {
                                        return $share->pairedWithThis ? $share->pairedWithThis->pluck('id') : collect();
                                    });
                                    $payments            = \App\Models\UserSharePayment::whereIn('user_share_pair_id', $pairedIds)->get();
                                    $pairedWithIsAllPaid = $pairedShare->flatMap(function($share) {
                                        return $share->pairedWithThis ? $share->pairedWithThis->where('is_paid', 0) : collect();
                                    })->count();
                                    $tradePrice          = $firstShare->trade ? $firstShare->trade->price : 0;
                                    $currentBusinessProfile = json_decode(auth()->user()->business_profile);
                                    
                                    // Get appropriate payment details using PaymentHelper
                                    $paymentDetails = \App\Helpers\PaymentHelper::getPaymentDetails($businessProfile, $user);
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
                                            <p class="text-muted mb-0 fs-13">{{ $user->username }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-medium">{{ $paymentDetails['payment_name'] }}</span>
                                        <p class="text-muted mb-0 fs-13">{{ $paymentDetails['payment_number'] }}</p>
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
                                    @elseif($firstShare->pairedWithThis && $firstShare->pairedWithThis->isNotEmpty() && \Carbon\Carbon::parse($firstShare->pairedWithThis->first()->created_at)->addMinutes($share->payment_deadline_minutes ?? 60) >= now() && $pairedWithIsAllPaid != 0)
                                        <div class="text-center">
                                            <button type="button" class="btn btn-soft-success btn-sm mb-2" 
                                                    data-bs-toggle="modal" data-bs-target="#cleanPaymentModal{{$user->id ."-". $firstShare->id }}">
                                                <i class="ri-secure-payment-line align-middle me-1"></i>Pay Now
                                            </button>
                                            <br>
                                            <small class="text-muted">
                                                <i class="ri-information-line me-1"></i>View timer on <a href="{{ route('users.bought_shares') }}" class="text-decoration-none">main page</a>
                                            </small>
                                        </div>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2">
                                            <i class="ri-close-circle-line align-middle me-1"></i>Payment Expired
                                        </span>
                                    @endif
                                </td>
                            </tr>


                            {{-- Include Clean Payment Form Component --}}
                            @include('components.payment-submit-form-clean', [
                                'user' => $user,
                                'share' => $firstShare,
                                'businessProfile' => (object)[
                                    'mpesa_name' => $paymentDetails['payment_name'],
                                    'mpesa_no' => $paymentDetails['payment_number']
                                ],
                                'totalShare' => $totalShare,
                                'tradePrice' => $tradePrice,
                                'pairedIds' => $pairedIds->toArray()
                            ])
                            
                            {{-- listing model --}}
                            <div class="modal fade" id="listModal{{$user->id ."-". $firstShare->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="staticBackdropLabel">Share Details for <b>{{$user->name}}</b></h5>
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
                @endif
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
    <!-- iOS Payment Modal Compatibility Fixes -->
    <link href="{{ asset('assets/css/ios-payment-modal-fix.css') }}?v={{ time() }}" rel="stylesheet">
    
    <!-- Afresh Payment Form JavaScript -->
    <script src="{{ asset('assets/js/payment-form-afresh.js') }}?v={{ time() }}"></script>
    
    <!-- iOS Payment Modal JavaScript Fixes -->
    <script src="{{ asset('assets/js/ios-payment-modal-fix.js') }}?v={{ time() }}"></script>
    
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

            // Animate statistics cards on load - DISABLED to prevent flickering
            // animateStatsCards();
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

        // Copy to clipboard functionality
        function copyToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    showToast('Success!', 'M-Pesa number copied to clipboard', 'success');
                }, function(err) {
                    showToast('Error!', 'Failed to copy M-Pesa number', 'error');
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    showToast('Success!', 'M-Pesa number copied to clipboard', 'success');
                } catch (err) {
                    showToast('Error!', 'Failed to copy M-Pesa number', 'error');
                } finally {
                    textArea.remove();
                }
            }
        }

        // Enhanced and robust form validation with multiple field detection strategies
        function validatePaymentForm(form) {
            console.log('=== FORM VALIDATION DEBUG ===');
            
            if (!form) {
                console.error('Form not provided');
                alert('Form not found. Please refresh the page and try again.');
                return false;
            }
            
            console.log('Form provided:', form);
            console.log('Form ID:', form.id);
            console.log('Form innerHTML preview:', form.innerHTML.substring(0, 500) + '...');
            
            // Wait for DOM to be fully ready if in modal
            if (form.closest('.modal')) {
                // Small delay to ensure modal content is fully rendered
                const modal = form.closest('.modal');
                if (!modal.classList.contains('show')) {
                    console.log('Modal not fully shown, waiting...');
                    setTimeout(() => validatePaymentForm(form), 100);
                    return false;
                }
            }
            
            // Try multiple strategies to find the transaction ID field
            let txsIdField = null;
            
            // Strategy 1: Query by name attribute
            txsIdField = form.querySelector('[name="txs_id"]');
            console.log('Strategy 1 - Query by name "txs_id":', txsIdField);
            
            // Strategy 2: Query by class if Strategy 1 failed
            if (!txsIdField) {
                txsIdField = form.querySelector('.transaction-input');
                console.log('Strategy 2 - Query by class "transaction-input":', txsIdField);
            }
            
            // Strategy 3: Query by ID pattern if Strategy 2 failed
            if (!txsIdField) {
                txsIdField = form.querySelector('[id*="txsIdField"]');
                console.log('Strategy 3 - Query by ID pattern "txsIdField":', txsIdField);
            }
            
            // Strategy 4: Query all inputs and find by name
            if (!txsIdField) {
                const allInputs = form.querySelectorAll('input');
                console.log('Strategy 4 - All inputs in form:', allInputs.length);
                for (let input of allInputs) {
                    console.log('Input:', input.tagName, 'Name:', input.name, 'Type:', input.type, 'ID:', input.id);
                    if (input.name === 'txs_id') {
                        txsIdField = input;
                        console.log('Found txs_id field via Strategy 4:', txsIdField);
                        break;
                    }
                }
            }
            
            // Strategy 5: Try document-wide search as fallback
            if (!txsIdField) {
                console.log('Strategy 5 - Document-wide search for txs_id field');
                txsIdField = document.querySelector('input[name="txs_id"]');
                console.log('Strategy 5 result:', txsIdField);
            }
            
            if (!txsIdField) {
                console.error('Transaction ID field not found in form after all strategies');
                console.log('Form HTML:', form.innerHTML);
                // Since transaction ID is optional, continue with form submission
                console.log('Transaction ID field not found, but continuing since it\'s optional');
                return true;
            }
            
            console.log('Transaction ID field found:', txsIdField);
            console.log('Field name:', txsIdField.name);
            console.log('Field type:', txsIdField.type);
            console.log('Field ID:', txsIdField.id);
            console.log('Field classes:', txsIdField.className);
            
            const value = txsIdField.value ? txsIdField.value.trim() : '';
            
            console.log('Field value (raw):', txsIdField.value);
            console.log('Field value (trimmed):', value);
            
            // Remove any previous validation classes
            txsIdField.classList.remove('is-invalid', 'is-valid');
            
            // Transaction ID is now required
            if (!value || value.length === 0) {
                txsIdField.classList.add('is-invalid');
                txsIdField.focus();
                alert('Please enter the M-Pesa Transaction ID. This field is required.');
                console.log('Validation failed: Transaction ID is required');
                return false;
            }
            
            // Validate transaction ID format
            const txsIdPattern = /^[A-Za-z0-9\s\-_.]{4,30}$/;
            if (!txsIdPattern.test(value)) {
                txsIdField.classList.add('is-invalid');
                txsIdField.focus();
                alert('Invalid M-Pesa Transaction ID format. Please enter a valid transaction ID (4-30 characters, letters, numbers, spaces, hyphens allowed)');
                console.log('Validation failed: invalid format. Pattern:', txsIdPattern, 'Value:', value);
                return false;
            } else {
                txsIdField.classList.add('is-valid');
            }
            
            console.log('Validation passed successfully');
            console.log('=== END FORM VALIDATION DEBUG ===');
            return true;
        }

        // Simplified form submission handler
        function handleFormSubmission(event, form) {
            console.log('Form submission handler called for:', form.id);
            event.preventDefault();
            
            if (!validatePaymentForm(form)) {
                return false;
            }
            
            const submitBtn = form.querySelector('.submit-payment-btn');
            const spinner = submitBtn ? submitBtn.querySelector('.spinner-border') : null;
            
            // Show confirmation dialog - DISABLED
            const confirmed = true; // confirm('Are you sure you want to submit this payment information? Please ensure all details are correct.');
            
            if (confirmed) {
                console.log('User confirmed submission, submitting form...');
                
                // Show loading state
                if (submitBtn) {
                    submitBtn.disabled = true;
                    if (spinner) {
                        spinner.classList.remove('d-none');
                    }
                }
                
                // Submit the form
                try {
                    form.submit();
                    console.log('Form submitted successfully');
                } catch (error) {
                    console.error('Error submitting form:', error);
                    alert('Error submitting form: ' + error.message);
                    
                    // Reset loading state on error
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        if (spinner) {
                            spinner.classList.add('d-none');
                        }
                    }
                }
            } else {
                console.log('User cancelled submission');
            }
        }

        // Real-time transaction ID validation
        function setupTransactionIdValidation() {
            document.querySelectorAll('.transaction-input').forEach(input => {
                input.addEventListener('input', function() {
                    const value = this.value.trim();
                    // M-Pesa IDs can contain letters, numbers, spaces, hyphens, and other common characters
                    const pattern = /^[A-Za-z0-9\s\-_.]{4,30}$/;
                    
                    this.classList.remove('is-invalid', 'is-valid');
                    
                    if (value.length === 0) {
                        return; // Don't validate empty field
                    }
                    
                    if (pattern.test(value)) {
                        this.classList.add('is-valid');
                    } else if (value.length >= 3) { // Start showing validation after 3 characters
                        this.classList.add('is-invalid');
                    }
                });
                
                // Clear validation on focus
                input.addEventListener('focus', function() {
                    this.classList.remove('is-invalid');
                });
            });
        }

        // Enhanced modal event handling
        function setupModalEventHandlers() {
            document.querySelectorAll('.payment-modal').forEach(modal => {
                modal.addEventListener('shown.bs.modal', function() {
                    console.log('Payment modal opened');
                    
                    // Focus on transaction ID field when modal opens
                    const txsIdInput = this.querySelector('.transaction-input');
                    if (txsIdInput) {
                        setTimeout(() => txsIdInput.focus(), 300);
                    }
                });
                
                modal.addEventListener('hidden.bs.modal', function() {
                    // Reset form validation states when modal closes
                    const form = this.querySelector('.payment-form');
                    if (form) {
                        form.classList.remove('payment-form-loading');
                        form.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
                            field.classList.remove('is-valid', 'is-invalid');
                        });
                        
                        // Reset submit button
                        const submitBtn = form.querySelector('.submit-payment-btn');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            const spinner = submitBtn.querySelector('.spinner-border');
                            if (spinner) {
                                spinner.classList.add('d-none');
                            }
                        }
                    }
                });
            });
        }

        // Setup direct form handlers without cloning
        function setupDirectFormHandlers() {
            document.querySelectorAll('.payment-form').forEach(form => {
                console.log('Setting up direct handler for form:', form.id);
                
                // Add event listener without cloning (to preserve user input)
                form.addEventListener('submit', function(e) {
                    console.log('Direct form handler triggered for:', this.id);
                    handleFormSubmission(e, this);
                });
            });
        }
        
        // Add click events to action buttons
        document.addEventListener('DOMContentLoaded', function() {
            const refreshBtn = document.querySelector('[title="Refresh Data"]');
            const exportBtn = document.querySelector('[title="Export"]');
            
            if (refreshBtn) refreshBtn.addEventListener('click', refreshData);
            if (exportBtn) exportBtn.addEventListener('click', exportData);
            
            // Initialize enhanced functionality
            setupTransactionIdValidation();
            setupModalEventHandlers();
            setupDirectFormHandlers();
            
            // Initialize animations when page loads - DISABLED to prevent flickering
            // setTimeout(animateStatsCards, 300);
            
            // Add smooth scrolling for better UX
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });

        // Removed modal preloading to fix flickering issue
        // The preloading on mouseenter was causing visual glitches
        
        // Primary button click handler - this is the main entry point
        function handleButtonClick(event, formId) {
            console.log('=== PRIMARY BUTTON CLICK HANDLER TRIGGERED ===');
            console.log('Form ID requested:', formId);
            console.log('Event target:', event.target);
            
            event.preventDefault();
            event.stopPropagation();
            
            // Wait a tiny bit to ensure DOM is ready
            setTimeout(() => {
                // Get the form directly by ID
                const form = document.getElementById(formId);
                console.log('Attempting to find form with ID:', formId);
                console.log('Form found:', form);
                
                if (!form) {
                    // Alternative method: get form from the modal that contains the button
                    console.log('Direct ID lookup failed, trying alternative methods...');
                    const button = event.target;
                    const modal = button.closest('.modal');
                    const alternativeForm = modal ? modal.querySelector('.payment-form') : null;
                    
                    console.log('Button:', button);
                    console.log('Modal:', modal);
                    console.log('Alternative form:', alternativeForm);
                    
                    if (!alternativeForm) {
                        alert('Form not found: ' + formId + '. Please close the modal and try again.');
                        console.error('Form not found with any method. FormId:', formId);
                        return false;
                    }
                    
                    console.log('Using alternative form:', alternativeForm);
                    console.log('Alternative form ID:', alternativeForm.id);
                    console.log('Alternative form action:', alternativeForm.action);
                    
                    // Use the alternative form
                    handleFormValidationAndSubmission(alternativeForm, event.target);
                    return;
                }
                
                console.log('Form found successfully:', form);
                console.log('Form ID:', form.id);
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);
                
                // Use the found form
                handleFormValidationAndSubmission(form, event.target);
                
            }, 50); // Small delay to ensure modal is fully rendered
        }
        
        // Separate function to handle form validation and submission
        function handleFormValidationAndSubmission(form, submitButton) {
            console.log('=== HANDLING FORM VALIDATION AND SUBMISSION ===');
            console.log('Form:', form);
            console.log('Submit button:', submitButton);
            
            // Validate the form using the enhanced validator
            if (!validatePaymentForm(form)) {
                console.log('Form validation failed');
                return false;
            }
            
            console.log('Form validation passed');
            
            
            // Show confirmation - DISABLED
            // if (!confirm('Are you sure you want to submit this payment information?')) {
            //     console.log('User cancelled submission');
            //     return false;
            // }
            
            console.log('User confirmed submission');
            
            // Show loading state
            const spinner = submitButton.querySelector('.spinner-border');
            submitButton.disabled = true;
            if (spinner) spinner.classList.remove('d-none');
            
            console.log('About to submit form:', form.id);
            console.log('Form action URL:', form.action);
            
            // Submit the form with delayed notification sound
            try {
                form.submit();
                console.log('Form submitted successfully');
                
                
            } catch (error) {
                console.error('Form submission error:', error);
                alert('Error submitting form: ' + error.message);
                
                
                // Reset loading state on error
                submitButton.disabled = false;
                if (spinner) spinner.classList.add('d-none');
            }
        }
        
        // SIMPLE AND DIRECT FORM SUBMISSION FUNCTION - IMPROVED APPROACH
        function submitPaymentForm(formId, buttonElement) {
            console.log('=== SIMPLE FORM SUBMISSION START ===');
            console.log('Form ID:', formId);
            console.log('Button Element:', buttonElement);
            
            // Get form element with more reliable methods
            let form = document.getElementById(formId);
            
            // Alternative: try to find form from the button context if ID lookup fails
            if (!form && buttonElement) {
                const modal = buttonElement.closest('.modal');
                if (modal) {
                    form = modal.querySelector('form.payment-form');
                    console.log('Form found via modal context:', form);
                }
            }
            
            // Final check for form
            if (!form) {
                console.error('Form not found:', formId);
                alert('Form not found. Please refresh the page.');
                return;
            }
            
            console.log('Form found:', form);
            console.log('Form action:', form.action);
            
            // Enhanced transaction ID field detection - try multiple methods
            console.log('Searching for transaction ID field in form...');
            
            // Method 1: By name attribute
            let txsIdField = form.querySelector('input[name="txs_id"]');
            console.log('Method 1 (by name):', txsIdField);
            
            // Method 2: By class
            if (!txsIdField) {
                txsIdField = form.querySelector('.transaction-input');
                console.log('Method 2 (by class):', txsIdField);
            }
            
            // Method 3: By ID pattern
            if (!txsIdField) {
                const inputs = form.querySelectorAll('input[id*="txsIdField"]');
                console.log('Method 3 (by ID pattern) found:', inputs.length, 'matches');
                if (inputs.length > 0) {
                    txsIdField = inputs[0];
                }
            }
            
            // Method 4: Get all inputs and check attributes
            if (!txsIdField) {
                const allInputs = Array.from(form.querySelectorAll('input'));
                console.log('Method 4: Scanning all inputs:', allInputs.length);
                
                // Look for input with ID containing 'txsId' or name='txs_id'
                txsIdField = allInputs.find(input => 
                    (input.id && input.id.includes('txsId')) || 
                    (input.name && input.name === 'txs_id') ||
                    input.classList.contains('transaction-input')
                );
            }
            
            // Transaction ID field handling - more robust to support both required and optional cases
            const fieldIsRequired = form.querySelector('input[name="txs_id"][required]') !== null;
            console.log('Field is required:', fieldIsRequired);
            
            if (!txsIdField) {
                if (fieldIsRequired) {
                    // Only block if field is actually required
                    console.error('Transaction ID field not found - this is required');
                    alert('Transaction ID field not found. Please refresh the page and try again.');
                    return;
                } else {
                    // For optional case, continue without blocking
                    console.warn('Transaction ID field not found, but continuing since it\'s optional');
                }
            } else {
                console.log('Transaction ID field found:', txsIdField);
                console.log('Field ID:', txsIdField.id);
                console.log('Field value:', txsIdField.value);
                
                // Only validate if field exists and is required or has a value
                const value = txsIdField.value ? txsIdField.value.trim() : '';
                
                // If field is required or has content, validate it
                if (fieldIsRequired || value.length > 0) {
                    if (fieldIsRequired && (!value || value.length === 0)) {
                        alert('Please enter the M-Pesa Transaction ID. This field is required.');
                        txsIdField.focus();
                        txsIdField.classList.add('is-invalid');
                        return;
                    }
                    
                    // Validate format if value is provided
                    if (value.length > 0) {
                        const pattern = /^[A-Za-z0-9\s\-_.]{4,30}$/;
                        if (!pattern.test(value)) {
                            alert('Invalid M-Pesa Transaction ID format. Please use 4-30 characters (letters, numbers, spaces, hyphens allowed)');
                            txsIdField.focus();
                            txsIdField.classList.add('is-invalid');
                            return;
                        } else {
                            txsIdField.classList.add('is-valid');
                        }
                    }
                }
            }
            
            // Validation for required fields
            const requiredFields = form.querySelectorAll('[required]');
            let allValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value || !field.value.trim()) {
                    field.classList.add('is-invalid');
                    allValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!allValid) {
                alert('Please fill in all required fields.');
                return;
            }
            
            console.log('All validations passed');
            
            // Confirmation - DISABLED
            // if (!confirm('Are you sure you want to submit this payment information?')) {
            //     return;
            // }
            
            // Show loading state
            buttonElement.disabled = true;
            const spinner = buttonElement.querySelector('.spinner-border');
            if (spinner) {
                spinner.classList.remove('d-none');
            }
            
            // Save original button content
            const originalContent = buttonElement.innerHTML;
            buttonElement.innerHTML = '<i class="ri-loader-2-line me-2 spinner-border spinner-border-sm"></i>Submitting...';
            
            // Submit form with error handling and delayed notification sound
            try {
                console.log('Submitting form...');
                form.submit();
                console.log('Form submitted successfully');
                
                
            } catch (error) {
                console.error('Form submission error:', error);
                alert('Error submitting form: ' + error.message);
                
                
                // Reset button state on error
                buttonElement.disabled = false;
                buttonElement.innerHTML = originalContent;
                if (spinner) {
                    spinner.classList.add('d-none');
                }
            }
        }
        
        // Note: Timer functionality removed from individual share view to prevent conflicts
        // All payment timers are now managed from the main bought-shares page
        // This ensures consistency and eliminates timer conflicts between pages
        
        // Debug information on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== PAGE LOADED DEBUG INFO ===');
            console.log('Active payment timers:', Object.keys(window.paymentTimers));
            
            document.querySelectorAll('.payment-form').forEach(form => {
                console.log('Found payment form:', form.id);
                console.log('Form action:', form.action);
                
                const txsIdField = form.querySelector('[name="txs_id"]');
                console.log('Transaction ID field for', form.id, ':', txsIdField);
                if (txsIdField) {
                    console.log('Field ID:', txsIdField.id);
                    console.log('Field name:', txsIdField.name);
                }
            });
        });
        
    </script>

    <!-- Enhanced styles for Payment Modal and Performance -->
    <style>
        /* Disable specific animations causing flickering */
        .payment-modal *,
        .payment-modal *:hover,
        .table *,
        .table *:hover {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
        
        /* Override DataTables hover effects */
        #pairedSharesTable tbody tr:hover {
            background-color: rgba(40, 167, 69, 0.05) !important;
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
        
        .payment-modal .bg-gradient-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
        }
        
        .payment-modal .payment-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payment-modal .seller-avatar .avatar-lg {
            width: 80px;
            height: 80px;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .payment-modal .summary-item {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .payment-modal .summary-item:hover {
            transform: translateY(-2px);
        }
        
        .payment-modal .mpesa-number {
            background: #f8f9fa !important;
            border: 2px dashed #28a745 !important;
            border-radius: 10px !important;
        }
        
        .payment-modal .form-floating > .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .payment-modal .form-floating > .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.15);
        }
        
        .payment-modal .transaction-input:valid {
            border-color: #28a745;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.94-.94 1.38 1.38'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        .payment-modal .submit-payment-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .payment-modal .submit-payment-btn:hover {
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
        
        .payment-modal .submit-payment-btn:disabled {
            opacity: 0.7;
            transform: none;
        }
        
        /* Loading Animation */
        .payment-form-loading {
            position: relative;
            overflow: hidden;
        }
        
        .payment-form-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #4f46e5, transparent);
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        /* Responsive Design */
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
            
            .payment-modal .modal-xl {
                max-width: 95% !important;
            }
            
            .payment-modal .summary-item {
                padding: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .payment-modal .seller-avatar .avatar-lg {
                width: 60px;
                height: 60px;
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
            
            .payment-modal .modal-footer {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .payment-modal .modal-footer .btn {
                width: 100%;
            }
        }
        
        /* COMPREHENSIVE ANTI-FLICKERING STYLES */
        /* Disable ALL animations and transitions globally for this page */
        *, *::before, *::after {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
        
        /* Enhanced hover effects - removed transforms to prevent flickering */
        .table tbody tr:hover {
            background-color: rgba(40, 167, 69, 0.05) !important;
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
        
        .badge {
            font-weight: 500;
            font-size: 0.75rem;
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
        
        .cursor-pointer {
            cursor: pointer;
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
        
        .cursor-pointer:hover {
            /* Disabled transition to prevent flickering */
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
        
        .btn:hover {
            /* Disabled hover effects to prevent flickering */
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
        
        /* Disable Bootstrap and other library animations */
        .btn, .btn:hover, .btn:focus, .btn:active {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
        
        .card, .card:hover {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
        
        .modal, .modal-dialog, .modal-content {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
        
        .fade {
            animation: none !important;
            transition: none !important;
            transform: none !important;
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
        
        .table-responsive {
            overflow-x: auto;
        }
        
        /* Performance optimizations */
        .payment-modal .modal-content {
            transform: translateZ(0);
            backface-visibility: hidden;
            perspective: 1000px;
        }
        
        .payment-modal .form-floating > .form-control {
            will-change: transform, box-shadow, border-color;
        }
        
        .payment-modal .submit-payment-btn {
            will-change: transform, box-shadow;
        }
        
        /* Payment Deadline Timer Styling */
        .payment-deadline-timer {
            background: linear-gradient(135deg, var(--theme-primary, #405189) 0%, var(--theme-secondary, #3577f1) 100%);
            color: white !important;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 11px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            box-shadow: 0 2px 4px rgba(64, 81, 137, 0.3);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }
        
        /* Timer color variations based on urgency */
        .payment-deadline-timer.urgent {
            background: linear-gradient(135deg, #f06548 0%, #dc2626 100%);
            box-shadow: 0 2px 4px rgba(240, 101, 72, 0.3);
            animation: pulse 1s infinite;
        }
        
        .payment-deadline-timer.warning {
            background: linear-gradient(135deg, #f7b84b 0%, #d97706 100%);
            box-shadow: 0 2px 4px rgba(247, 184, 75, 0.3);
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 2px 4px rgba(240, 101, 72, 0.3);
            }
            50% {
                box-shadow: 0 4px 12px rgba(240, 101, 72, 0.6);
                transform: translateY(-1px) scale(1.02);
            }
            100% {
                box-shadow: 0 2px 4px rgba(240, 101, 72, 0.3);
            }
        }
    </style>
@endsection
