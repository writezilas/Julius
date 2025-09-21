@extends('layouts.master')
@section('title') {{$pageTitle}}  @endsection
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            color: white;
        }
    </style>
@endsection
@section('content')

    @component('components.breadcrumb')
        @slot('li_1') @lang('translation.dashboard') @endslot
        @slot('title') {{$pageTitle}} @endslot
    @endcomponent

    @php
        // Use ShareStatusService for consistent status across all pages
        $shareStatusService = app(\App\Services\ShareStatusService::class);
        $statusInfo = $shareStatusService->getShareStatus($share, 'sold');
        $stats = $shareStatusService->getPairingStats($share);
        
        // Only load pair history if we should show it (to prevent confusion for transitioned shares)
        $pairedShares = collect(); // Default empty collection
        $totalBuyers = 0;
        $paidTransactions = 0;
        $pendingTransactions = 0;
        $totalAmount = 0;
        $validAmount = 0; // Amount from valid (non-failed) pairings only
        
        if (isset($shouldShowPairHistory) && $shouldShowPairHistory) {
            // Get all paired shares and organize them - successful on top, failed at bottom
            $allPairedShares = \App\Models\UserSharePair::where('paired_user_share_id', $share->id)
                ->with(['pairedUserShare', 'pairedUserShare.user'])
                ->get();
                
            // Create two collections - one for successful pairings, one for failed ones
            $successfulPairings = collect();
            $failedPairings = collect();
            
            foreach($allPairedShares as $pairedShare) {
                $payment = \App\Models\UserSharePayment::where('user_share_pair_id', $pairedShare->id)
                    ->orderBy('id', 'desc')
                    ->first();
                
                // Always include the pairing in total display amount
                $totalAmount += $pairedShare->share;
                
                // Check the buyer share status for proper categorization
                $buyerShare = $pairedShare->pairedUserShare;
                $buyerHasFailed = $buyerShare && $buyerShare->status === 'failed';
                
                // Determine if this is a successful or failed pairing
                $isSuccessful = false;
                $isExpired = false;
                
                if ($buyerHasFailed) {
                    // Buyer share has failed due to payment deadline expiry - exclude from valid statistics
                    $isSuccessful = false;
                    $isExpired = true;
                } else {
                    // Only count valid pairings in the statistics
                    $validAmount += $pairedShare->share;
                    
                    if ($pairedShare->is_paid == 1) {
                        // Payment confirmed
                        $isSuccessful = true;
                    } elseif ($payment && in_array($payment->status, ['paid', 'conformed'])) {
                        // Payment submitted and waiting for confirmation
                        $isSuccessful = true;
                    } elseif ($pairedShare->is_paid == 0) {
                        // Check if payment deadline has expired
                        $paymentDeadline = \Carbon\Carbon::parse($pairedShare->created_at)
                            ->addMinutes($share->payment_deadline_minutes ?? 60);
                        $isExpired = $paymentDeadline->isPast();
                        $isSuccessful = !$isExpired;
                    }
                }
                
                // If payment confirmed or in process, add to successful list
                // Otherwise, add to failed list
                if ($isSuccessful) {
                    $successfulPairings->push($pairedShare);
                    $paidTransactions++;
                } else if ($isExpired) {
                    $failedPairings->push($pairedShare);
                    $pendingTransactions++;
                } else {
                    // Still in payment window, add to successful list
                    $successfulPairings->push($pairedShare);
                    $paidTransactions++;
                }
            }
            
            // Sort each collection by created_at descending (newest first)
            $successfulPairings = $successfulPairings->sortByDesc('created_at');
            $failedPairings = $failedPairings->sortByDesc('created_at');
            
            // Combine the collections - successful on top, failed at bottom
            $pairedShares = $successfulPairings->concat($failedPairings);
            $totalBuyers = $pairedShares->count();
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
                                <i class="ri-share-line align-middle me-2"></i>
                                Share Details: {{$share->ticket_no}}
                            </h3>
                            @if (isset($shouldShowPairHistory) && $shouldShowPairHistory)
                                <p class="text-white-75 mb-0 fs-16">
                                    Track and manage your paired share transactions and payment confirmations
                                </p>
                            @else
                                <p class="text-white-75 mb-0 fs-16">
                                    This share has matured and is ready for new buyers. Old pair history is hidden to prevent confusion.
                                </p>
                            @endif
                        </div>
                        <div class="col-lg-4 text-end">
                            <div class="text-white">
                                <div class="mb-3">
                                    <span class="badge {{ $statusInfo['class'] }} fs-6 px-3 py-2" title="{{ $statusInfo['description'] }}">
                                        {{ $statusInfo['status'] }}
                                    </span>
                                </div>
                                <h6 class="text-white mb-0">{{ $statusInfo['description'] }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (isset($shouldShowPairHistory) && $shouldShowPairHistory)
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-uppercase fw-medium text-muted mb-0">Total Buyers</p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-2">
                                    <i class="ri-user-line fs-16"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-2">{{$totalBuyers}}</h4>
                            <span class="badge bg-primary-subtle text-primary"> 
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
                                <span class="avatar-title bg-success-subtle text-success rounded-2">
                                    <i class="ri-money-dollar-circle-line fs-16"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-2">{{number_format($totalAmount)}}</h4>
                            <span class="badge bg-success-subtle text-success"> 
                                <i class="ri-coins-line align-middle"></i> Shares 
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
                            <p class="text-uppercase fw-medium text-muted mb-0">Confirmed Payments</p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-success-subtle text-success rounded-2">
                                    <i class="ri-check-double-line fs-16"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-2">{{$stats['paid']}}</h4>
                            <span class="badge bg-success-subtle text-success"> 
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
                            <p class="text-uppercase fw-medium text-muted mb-0">Awaiting Confirmation</p>
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
                            <h4 class="fs-22 fw-semibold ff-secondary mb-2">{{$stats['awaiting_confirmation'] + $stats['unpaid']}}</h4>
                            <span class="badge bg-warning-subtle text-warning"> 
                                <i class="ri-hourglass-line align-middle"></i> Pending 
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if (isset($shouldShowPairHistory) && $shouldShowPairHistory)
    <!-- Paired Shares Table -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="ri-exchange-funds-line align-middle me-2 text-primary"></i>
                                Your Paired Shares
                            </h5>
                            <p class="text-muted mb-0">Latest successful pairings shown first, failed payments at bottom</p>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex flex-wrap align-items-center gap-1">
                                <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="tooltip" 
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
                    @if(count($pairedShares) > 0)
                        <div class="table-responsive">
                            <table id="pairedSharesTable" class="table table-hover align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="text-center">#</th>
                                        <th scope="col">
                                            <i class="ri-user-line align-middle me-1"></i>Buyer Details
                                        </th>
                                        <th scope="col">
                                            <i class="ri-smartphone-line align-middle me-1"></i>MPESA Info
                                        </th>
                                        <th scope="col">
                                            <i class="ri-coins-line align-middle me-1"></i>Amount
                                        </th>
                                        <th scope="col" class="text-center">
                                            <i class="ri-shield-check-line align-middle me-1"></i>Status
                                        </th>
                                        <th scope="col" class="text-center">
                                            <i class="ri-settings-line align-middle me-1"></i>Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $hasShownFailedHeader = false;
                                        $previousWasFailed = false;
                                    @endphp
                                    
                                    @foreach($pairedShares as $key => $pairedShare)
                                        @php
                                            $payment = \App\Models\UserSharePayment::where('user_share_pair_id', $pairedShare->id)->orderBy('id', 'desc')->first();
                                            
                                            // Check buyer share status for proper categorization
                                            $buyerShare = $pairedShare->pairedUserShare;
                                            $buyerHasFailed = $buyerShare && $buyerShare->status === 'failed';
                                            
                                            // Determine if this is a failed pairing for styling
                                            $isExpired = false;
                                            $currentIsFailed = false;
                                            
                                            if ($buyerHasFailed) {
                                                // Buyer share failed due to payment deadline expiry
                                                $currentIsFailed = true;
                                                $isExpired = true;
                                            } elseif ($pairedShare->is_paid == 2) {
                                                // Pairing explicitly marked as failed
                                                $currentIsFailed = true;
                                            } elseif ($pairedShare->is_paid == 0) {
                                                // Check if payment deadline has expired
                                                $paymentDeadline = \Carbon\Carbon::parse($pairedShare->created_at)
                                                    ->addMinutes($share->payment_deadline_minutes ?? 60);
                                                $isExpired = $paymentDeadline->isPast();
                                                $currentIsFailed = $isExpired;
                                            }
                                            
                                            $rowClass = '';
                                            if ($currentIsFailed) {
                                                $rowClass = 'table-row-failed';
                                            }
                                        @endphp
                                        
                                        {{-- Show section header when transitioning from successful to failed --}}
                                        @if ($currentIsFailed && !$hasShownFailedHeader && !$previousWasFailed)
                                            <tr class="table-divider">
                                                <td colspan="6" class="text-center py-3">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <hr class="flex-grow-1">
                                                        <span class="mx-3 text-muted small">
                                                            <i class="ri-close-circle-line me-1 text-danger"></i>
                                                            Failed/Expired Payments
                                                        </span>
                                                        <hr class="flex-grow-1">
                                                    </div>
                                                </td>
                                            </tr>
                                            @php $hasShownFailedHeader = true; @endphp
                                        @endif
                                        <tr class="{{ $rowClass }}">
                                            <td class="text-center">
                                                <span class="fw-medium text-primary">{{ $key + 1 }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-3">
                                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                            {{ strtoupper(substr($pairedShare->pairedUserShare->user->name, 0, 1)) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="fs-15 fw-semibold mb-0">{{ $pairedShare->pairedUserShare->user->name }}</h6>
                                                        <p class="text-muted mb-0 fs-13">{{ $pairedShare->pairedUserShare->user->username }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="fw-medium">{{ json_decode($pairedShare->pairedUserShare->user->business_profile)->mpesa_name ?? 'N/A' }}</span>
                                                    <p class="text-muted mb-0 fs-13">{{ json_decode($pairedShare->pairedUserShare->user->business_profile)->mpesa_no ?? 'N/A' }}</p>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-money-dollar-circle-line text-success me-2"></i>
                                                    <span class="fw-semibold fs-15">{{ number_format($pairedShare->share) }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($buyerHasFailed)
                                                    <span class="badge bg-danger-subtle text-danger px-3 py-2">
                                                        <i class="ri-user-x-line align-middle me-1"></i>Buyer Share Failed
                                                    </span>
                                                @elseif($payment && $payment->status === 'paid')
                                                    <span class="badge bg-warning-subtle text-warning px-3 py-2">
                                                        <i class="ri-time-line align-middle me-1"></i>Awaiting Confirmation
                                                    </span>
                                                @elseif($payment && $payment->status === 'conformed')
                                                    <span class="badge bg-success-subtle text-success px-3 py-2">
                                                        <i class="ri-check-double-line align-middle me-1"></i>Payment Confirmed
                                                    </span>
                                                @elseif($pairedShare->is_paid === 1)
                                                    <span class="badge bg-success-subtle text-success px-3 py-2">
                                                        <i class="ri-check-double-line align-middle me-1"></i>Payment Completed
                                                    </span>
                                                @elseif($pairedShare->is_paid === 0)
                                                    @php
                                                        // Check if payment deadline has expired
                                                        $paymentDeadline = \Carbon\Carbon::parse($pairedShare->created_at)
                                                            ->addMinutes($share->payment_deadline_minutes ?? 60);
                                                        $isDeadlineExpired = $paymentDeadline->isPast();
                                                    @endphp
                                                    @if($isDeadlineExpired)
                                                        <span class="badge bg-danger-subtle text-danger px-3 py-2">
                                                            <i class="ri-close-circle-line align-middle me-1"></i>Payment Failed
                                                        </span>
                                                    @else
                                                        <span class="badge bg-info-subtle text-info px-3 py-2">
                                                            <i class="ri-hourglass-line align-middle me-1"></i>Waiting for Payment
                                                        </span>
                                                    @endif
                                                @elseif($pairedShare->is_paid === 2)
                                                    <span class="badge bg-danger-subtle text-danger px-3 py-2">
                                                        <i class="ri-close-circle-line align-middle me-1"></i>Payment Failed
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2">
                                                        <i class="ri-question-line align-middle me-1"></i>Unknown Status
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-soft-primary btn-sm" 
                                                        data-bs-toggle="modal" data-bs-target="#soldShareDetails{{ $pairedShare->id }}">
                                                    <i class="ri-eye-line align-middle me-1"></i>View Details
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        @php $previousWasFailed = $currentIsFailed; @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ri-exchange-funds-line display-4 text-muted"></i>
                            </div>
                            <h5 class="mt-2">No Paired Shares Found!</h5>
                            <p class="text-muted mb-0">There are no buyers paired with this share yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Share Maturity Message -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="ri-check-double-line display-3 text-success"></i>
                    </div>
                    <h4 class="text-success mb-3">Share Has Matured</h4>
                    <p class="text-muted mb-4">
                        This share has completed its maturity period and is now ready for new buyers.
                    </p>
                    <div class="alert alert-info" role="alert">
                        <i class="ri-information-line align-middle me-2"></i>
                        Once new buyers are available in the market, pairing information will be displayed here.
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if (isset($shouldShowPairHistory) && $shouldShowPairHistory)
    <!-- Payment Details Modals -->
    @foreach($pairedShares as $pairedShare)
        @php
            $payment = \App\Models\UserSharePayment::where('user_share_pair_id', $pairedShare->id)->orderBy('id', 'desc')->first();
        @endphp
        @if($payment)
            <div class="modal fade" id="soldShareDetails{{ $pairedShare->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="paymentModalLabel{{ $pairedShare->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary bg-gradient">
                            <h5 class="modal-title text-white" id="paymentModalLabel{{ $pairedShare->id }}">
                                <i class="ri-secure-payment-line align-middle me-2"></i>
                                Payment Confirmation Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Payment Status Banner -->
                            @if($payment->status === 'conformed')
                                <div class="alert alert-success border-0 rounded-3 mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="ri-check-double-line fs-20"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Payment Completed Successfully!</h6>
                                            <p class="mb-0">Thank you for confirming the payment. The transaction is now complete.</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning border-0 rounded-3 mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="ri-time-line fs-20"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Payment Awaiting Confirmation</h6>
                                            <p class="mb-0">Please review the payment details below and confirm if the payment is correct.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Payment Details Card -->
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="ri-bill-line align-middle me-2 text-primary"></i>
                                        Transaction Information
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-user-line text-muted me-2"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Sender Name</small>
                                                    <p class="fw-medium mb-0">{{ $payment->name }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-smartphone-line text-muted me-2"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Phone Number</small>
                                                    <p class="fw-medium mb-0">{{ $payment->number }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-money-dollar-circle-line text-success me-2"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Amount</small>
                                                    <p class="fw-medium mb-0 text-success fs-16">{{ formatPrice($payment->amount) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-hashtag text-muted me-2"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Transaction ID</small>
                                                    <p class="fw-medium mb-0 font-monospace">{{ $payment->txs_id }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        @if($payment->note_by_sender)
                                        <div class="col-12">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0">
                                                    <i class="ri-chat-3-line text-muted me-2 mt-1"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Sender Note</small>
                                                    <p class="mb-0">{{ $payment->note_by_sender }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($payment->status !== 'conformed')
                                <!-- Confirmation Form -->
                                <div class="mt-4">
                                    <form id="paymentApproveForm{{$payment->id}}" action="{{ route('user.share.paymentApprove') }}" method="post">
                                        @csrf
                                        <input type="hidden" value="{{ $payment->id }}" name="paymentId">
                                        <div class="mb-3">
                                            <label for="note_by_receiver_approve_{{ $payment->id }}" class="form-label">
                                                <i class="ri-message-2-line align-middle me-1"></i>
                                                Your Comment <small class="text-muted">(optional)</small>
                                            </label>
                                            <textarea name="note_by_receiver" id="note_by_receiver_approve_{{ $payment->id }}" class="form-control" rows="3" 
                                                      placeholder="Add any comments about this payment..."></textarea>
                                        </div>
                                    </form>
                                    
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            @if($payment->status === 'conformed')
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="ri-close-line align-middle me-1"></i>Close
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="ri-close-line align-middle me-1"></i>Cancel
                                </button>
                                <button type="button" onclick="handlePaymentConformSubmit({{ $payment->id }})" 
                                        class="btn btn-success subBtn-{{$payment->id}}">
                                    <i class="ri-check-double-line align-middle me-1"></i>Confirm Payment
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
    @endif

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
                        searchPlaceholder: "Search transactions...",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ transactions",
                        infoEmpty: "No transactions found",
                        infoFiltered: "(filtered from _MAX_ total transactions)"
                    },
                    columnDefs: [
                        { orderable: false, targets: [0, 5] }, // Disable sorting on # and Action columns
                        { searchable: false, targets: [0, 5] }  // Disable search on # and Action columns
                    ],
                    order: [] // Disable default ordering to preserve custom PHP ordering
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

        // Enhanced payment confirmation function with audio notifications
        function handlePaymentConformSubmit(paymentId) {
            const submitBtn = $('.subBtn-' + paymentId);
            const originalText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="ri-loader-2-line align-middle me-1 spinner-border spinner-border-sm"></i>Processing...');
            
            // Add confirmation dialog
            if (confirm('Are you sure you want to confirm this payment? This action cannot be undone.')) {
                // Validation successful
                console.log('Payment confirmation validated');
                
                // Submit the form and schedule delayed notification sound
                try {
                    $('#paymentApproveForm' + paymentId).submit();
                    
                    console.log('Payment confirmation form submitted');
                    
                } catch (error) {
                    console.error('Form submission error:', error);
                    
                    
                    // Reset button state on error
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                }
            } else {
                // Reset button state if cancelled
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
            }
        }
        

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

        // Copy transaction ID to clipboard
        function copyTransactionId(txId) {
            navigator.clipboard.writeText(txId).then(function() {
                showToast('Success!', 'Transaction ID copied to clipboard', 'success');
            }, function(err) {
                showToast('Error!', 'Failed to copy transaction ID', 'error');
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
            
            .card-body .row.g-3 .col-md-6 {
                margin-bottom: 1rem;
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
            background-color: rgba(13, 110, 253, 0.05);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        /* Failed pairing row styling */
        .table-row-failed {
            background-color: rgba(220, 53, 69, 0.04);
            border-left: 3px solid #dc3545;
            opacity: 0.75;
        }
        
        .table-row-failed:hover {
            background-color: rgba(220, 53, 69, 0.08);
        }
        
        /* Table divider styling */
        .table-divider {
            border-top: 2px solid #e9ecef;
        }
        
        .table-divider td {
            border: none !important;
            background-color: #f8f9fa;
        }
        
        .table-divider hr {
            margin: 0;
            border-color: #dee2e6;
        }
        
        .badge {
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .modal-content {
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .alert {
            border-left: 4px solid currentColor;
        }
        
        .font-monospace {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            background-color: rgba(108, 117, 125, 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        
        .spinner-border-sm {
            animation: spinner-border 0.75s linear infinite;
        }
        
        @keyframes spinner-border {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endsection
