@extends('layouts.master')
@section('title') {{$pageTitle}} @endsection
@section('css')
<style>
    .page-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border-radius: 12px;
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(79, 70, 229, 0.15);
    }
    
    .stats-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    
    .stats-card.total { border-left-color: #3b82f6; }
    .stats-card.today { border-left-color: #10b981; }
    .stats-card.week { border-left-color: #f59e0b; }
    
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }
    
    .payment-item {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin-bottom: 1rem;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .payment-item:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border-color: #d1d5db;
        transform: translateY(-1px);
    }
    
    .customer-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #4f46e5;
        border: 2px solid #e5e7eb;
    }
    
    .ticket-badge {
        background: #f8fafc;
        color: #475569;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.875rem;
        border: 1px solid #e2e8f0;
    }
    
    .status-badge {
        padding: 0.375rem 0.875rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.875rem;
        text-transform: capitalize;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-failed {
        background: #fecaca;
        color: #b91c1c;
    }
    
    .btn-action {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    
    .btn-review {
        background: #4f46e5;
        color: white;
        border: none;
    }
    
    .btn-review:hover {
        background: #4338ca;
        transform: translateY(-1px);
        color: white;
    }
    
    .amount-display {
        font-size: 1rem;
        font-weight: 600;
        color: #059669;
        word-break: break-all;
        line-height: 1.2;
    }
    
    /* Responsive font sizing for large amounts */
    @media (max-width: 768px) {
        .amount-display {
            font-size: 0.9rem;
        }
    }
    
    /* Additional styles for better text wrapping */
    .stat-number {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.1;
        word-break: break-all;
    }
    
    @media (max-width: 768px) {
        .stat-number {
            font-size: 1.5rem;
        }
    }
    
    .shares-display {
        font-weight: 600;
        color: #1f2937;
    }
    
    .trade-badge {
        background: #dbeafe;
        color: #1d4ed8;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
    }
    
    .empty-icon {
        width: 80px;
        height: 80px;
        background: #f3f4f6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 2rem;
        color: #9ca3af;
    }
    
    .pagination-wrapper {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 1rem;
        margin-top: 1.5rem;
    }
    
    /* Custom Pagination Styling */
    .pagination {
        margin: 0;
        justify-content: center;
    }
    
    .page-link {
        border: 1px solid #e5e7eb;
        color: #6b7280;
        padding: 0.5rem 0.75rem;
        margin: 0 2px;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .page-link:hover {
        background: #f3f4f6;
        color: #374151;
        border-color: #d1d5db;
    }
    
    .page-item.active .page-link {
        background: #4f46e5;
        border-color: #4f46e5;
        color: white;
    }
    
    .page-item.disabled .page-link {
        color: #d1d5db;
        background: white;
    }
</style>
@endsection

@section('content')
@component('components.breadcrumb')
@slot('li_1') Share Management @endslot
@slot('title') {{$pageTitle}} @endslot
@endcomponent

<div class="row">
    <div class="col-12">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="mb-2" style="font-size: 1.75rem; font-weight: 600;">{{$pageTitle}}</h1>
                    <p class="mb-0" style="opacity: 0.9;">Review and manage pending share payment confirmations</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light" onclick="location.reload()">
                        <i class="ri-refresh-line me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card stats-card total">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-number">{{$totalPending}}</div>
                                <div class="stat-label">Total Pending</div>
                            </div>
                            <div class="text-primary" style="font-size: 1.5rem;">
                                <i class="ri-time-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stats-card today">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-number">{{$totalToday}}</div>
                                <div class="stat-label">Today</div>
                            </div>
                            <div class="text-success" style="font-size: 1.5rem;">
                                <i class="ri-calendar-today-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stats-card week">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-number">{{$totalThisWeek}}</div>
                                <div class="stat-label">This Week</div>
                            </div>
                            <div class="text-warning" style="font-size: 1.5rem;">
                                <i class="ri-calendar-week-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card filter-card fade-in">
            <div class="card-header border-0">
                <h5 class="card-title mb-0">
                    <i class="ri-filter-2-line align-middle me-2"></i>Filters
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.share.pending-payment-confirmations') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Trade Type</label>
                            <select name="trade_id" class="form-select">
                                <option value="">All Trades</option>
                                @foreach($trades as $trade)
                                    <option value="{{ $trade->id }}" {{ request('trade_id') == $trade->id ? 'selected' : '' }}>
                                        {{ $trade->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Customer</label>
                            <input type="text" name="customer" class="form-control" placeholder="Search customer..." value="{{ request('customer') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ticket No</label>
                            <input type="text" name="ticket_no" class="form-control" placeholder="Search ticket..." value="{{ request('ticket_no') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="submit" class="btn btn-primary btn-filter">
                                    <i class="ri-search-line align-middle me-1"></i>Filter
                                </button>
                                <a href="{{ route('admin.share.pending-payment-confirmations') }}" class="btn btn-secondary btn-filter">
                                    <i class="ri-refresh-line align-middle me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payment Confirmations List -->
        @can('pending-payment-confirmation-view')
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <i class="ri-file-list-3-line align-middle me-2"></i>Payment Confirmations
            </h5>
            <span class="badge bg-light text-dark border">{{ $pendingShares->total() }} Total Records</span>
        </div>
        
        @if($pendingShares->count() > 0)
            @foreach($pendingShares as $share)
                @php
                    $payment = $share->payment;
                    $pairedShare = $share->pairedShare;
                    $customer = $pairedShare ? $pairedShare->user : null;
                    $trade = $pairedShare ? $pairedShare->trade : null;
                @endphp
                @if($payment && $pairedShare && $customer)
                <div class="payment-item">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="customer-avatar me-3">
                                            {{ strtoupper(substr($customer->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $customer->name ?? 'Unknown User' }}</h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="ticket-badge">{{ $pairedShare->ticket_no ?? 'N/A' }}</span>
                                                @if($trade)
                                                    <span class="trade-badge">{{ $trade->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div>
                                        <div class="text-muted mb-1" style="font-size: 0.875rem;">Shares & Amount</div>
                                        <div class="shares-display">{{ number_format($share->share) }} shares</div>
                                        <div class="amount-display">{{ formatPrice($payment->amount) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div>
                                        <div class="text-muted mb-1" style="font-size: 0.875rem;">Date & Status</div>
                                        <div class="mb-1">{{ $payment->created_at ? $payment->created_at->format('M d, Y') : 'N/A' }}</div>
                                        @php
                                            $statusClass = $payment->status == 'failed' ? 'status-failed' : 'status-pending';
                                            $statusText = $payment->status == 'failed' ? 'Failed' : 'Pending';
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-end">
                            @can('pending-payment-confirmation-approve')
                            <button type="button" class="btn btn-review btn-action" data-bs-toggle="modal" 
                                    data-bs-target="#paymentModal{{ $share->id }}">
                                <i class="ri-eye-line me-1"></i>Review Payment
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>

                                @if($payment && $pairedShare && $customer)
                                <!-- Payment Modal -->
                                <div class="modal fade" id="paymentModal{{ $share->id }}" data-bs-backdrop="static" 
                                     data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Payment Confirmation - {{ $pairedShare->ticket_no ?? 'N/A' }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Payment Details -->
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <h6 class="text-muted mb-3">Payment Information</h6>
                                                        <table class="table table-borderless table-sm">
                                                            <tr>
                                                                <td class="fw-medium">Sender Name:</td>
                                                                <td>{{ $payment->name ?? 'N/A' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="fw-medium">Sender Phone:</td>
                                                                <td>{{ $payment->number ?? 'N/A' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="fw-medium">Transaction ID:</td>
                                                                <td><code>{{ $payment->txs_id ?? 'N/A' }}</code></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="fw-medium">Amount:</td>
                                                                <td class="fw-bold text-success">{{ $payment->amount ? formatPrice($payment->amount) : 'N/A' }}</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="text-muted mb-3">Share Information</h6>
                                                        <table class="table table-borderless table-sm">
                                                            <tr>
                                                                <td class="fw-medium">Customer:</td>
                                                                <td>{{ $customer->name ?? 'Unknown' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="fw-medium">Shares:</td>
                                                                <td>{{ number_format($share->share) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="fw-medium">Trade Type:</td>
                                                                <td>{{ $trade ? $trade->name : 'Unknown' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="fw-medium">Date:</td>
                                                                <td>{{ $payment->created_at ? $payment->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                                @if($payment->note_by_sender)
                                                <div class="alert alert-info">
                                                    <h6 class="alert-heading mb-2">
                                                        <i class="ri-message-2-line align-middle me-1"></i>Note from Sender
                                                    </h6>
                                                    <p class="mb-0">{{ $payment->note_by_sender }}</p>
                                                </div>
                                                @endif

                                                <!-- Action Forms -->
                                                @can('pending-payment-confirmation-approve')
                                                <!-- Confirm Form -->
                                                <div id="confirmForm{{ $payment->id }}" style="display: block;">
                                                    <form id="paymentApproveForm{{ $payment->id }}" action="{{ route('share.paymentApprove') }}" method="post">
                                                        @csrf
                                                        <div class="form-group mb-3">
                                                            <label class="form-label">Admin Comment <small class="text-muted">(optional)</small></label>
                                                            <textarea name="note_by_receiver" class="form-control" rows="3" 
                                                                      placeholder="Add any comments about this approval..."></textarea>
                                                            <input type="hidden" value="{{ $payment->id }}" name="paymentId">
                                                            <input type="hidden" value="1" name="by_admin">
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" onclick="handlePaymentConformSubmit({{ $payment->id }})" 
                                                                    class="btn btn-success subBtn-{{ $payment->id }}">
                                                                <i class="ri-check-line align-middle me-1"></i>Confirm Payment
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>

                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                @endif
                @endforeach
            
            <!-- Pagination -->
            <div class="pagination-wrapper">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">
                            Showing {{ $pendingShares->firstItem() }} to {{ $pendingShares->lastItem() }} 
                            of {{ $pendingShares->total() }} results
                        </p>
                    </div>
                    <div>
                        {{ $pendingShares->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="ri-inbox-line"></i>
                </div>
                <h5 class="mb-2">No Pending Payments Found</h5>
                <p class="mb-0">There are no pending payment confirmations to display at this time.</p>
            </div>
        @endif
        @endcan
    </div>
</div>
@endsection

@section('script')
<script>
    // Payment form handling functions
    function handlePaymentConformSubmit(paymentId) {
        $('.subBtn-' + paymentId)
            .prop('disabled', true)
            .html('<i class="ri-loader-2-line align-middle me-1 spinner-border spinner-border-sm"></i>Processing...');
        $('#paymentApproveForm' + paymentId).submit();
    }

</script>
@endsection
