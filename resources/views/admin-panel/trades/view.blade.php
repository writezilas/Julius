@extends('layouts.master')
@section('title') {{$pageTitle}} @endsection

@section('css')
<link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<style>
.stats-card {
    border-left: 4px solid;
    border-radius: 8px;
    transition: transform 0.2s;
}
.stats-card:hover {
    transform: translateY(-2px);
}
.stats-card.primary { border-left-color: #405189; }
.stats-card.success { border-left-color: #0ab39c; }
.stats-card.warning { border-left-color: #f7b84b; }
.stats-card.info { border-left-color: #299cdb; }
.stats-card.danger { border-left-color: #f06548; }
.stats-card.secondary { border-left-color: #6c757d; }

.filter-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    color: white;
}

.filter-section .form-control,
.filter-section .form-select {
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
}

.filter-section .form-control::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.filter-section .form-control:focus,
.filter-section .form-select:focus {
    background-color: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
    box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.trade-info-card {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    border-radius: 10px;
}

.back-btn {
    margin-bottom: 20px;
}

.table td {
    vertical-align: middle;
}

.countdown-timer {
    min-width: 120px;
}

.countdown-display {
    font-family: monospace;
    font-size: 0.9rem;
    white-space: nowrap;
}

@media (max-width: 768px) {
    .stats-card h4 {
        font-size: 1.125rem;
    }
    
    .countdown-display {
        font-size: 0.8rem;
    }
}
</style>
@endsection

@section('content')
@component('components.breadcrumb')
@slot('li_1') @lang('translation.dashboard') @endslot
@slot('li_2') <a href="{{ route('admin.trade.index') }}">Trades</a> @endslot
@slot('title') {{$pageTitle}} @endslot
@endcomponent

<!-- Back Button -->
<div class="back-btn">
    <a href="{{ route('admin.trade.index') }}" class="btn btn-secondary">
        <i class="ri-arrow-left-line me-1"></i>Back to Trades
    </a>
</div>

<!-- Trade Information Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card trade-info-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">{{ $trade->name }}</h4>
                        <p class="mb-2"><strong>Slug:</strong> {{ $trade->slug }}</p>
                        <div class="row">
                            <div class="col-md-3">
                                <small class="text-muted d-block">Price</small>
                                <span class="h5 text-success">KSH {{ number_format($trade->price, 2) }}</span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Buying Price</small>
                                <span class="h5 text-info">KSH {{ number_format($trade->buying_price, 2) }}</span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Quantity</small>
                                <span class="h5">{{ number_format($trade->quantity) }}</span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Status</small>
                                @if($trade->status == 1)
                                    <span class="badge bg-success status-badge">Active</span>
                                @else
                                    <span class="badge bg-danger status-badge">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <small class="text-muted d-block">Created</small>
                        <span>{{ $trade->created_at->format('M d, Y h:i A') }}</span>
                        <br>
                        <small class="text-muted">Total Amount: <strong>KSH {{ number_format($stats['total_amount'], 2) }}</strong></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-6">
        <div class="card stats-card primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary rounded-circle fs-3">
                            <i class="ri-stack-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Total Shares</p>
                        <h4 class="mb-0">{{ number_format($stats['total_shares']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning rounded-circle fs-3">
                            <i class="ri-time-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Pending</p>
                        <h4 class="mb-0 text-warning">{{ number_format($stats['pending_shares']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6">
        <div class="card stats-card info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info rounded-circle fs-3">
                            <i class="ri-loader-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Pairing</p>
                        <h4 class="mb-0 text-info">{{ number_format($stats['pairing_shares']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6">
        <div class="card stats-card secondary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-secondary rounded-circle fs-3">
                            <i class="ri-links-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Paired</p>
                        <h4 class="mb-0 text-secondary">{{ number_format($stats['paired_shares']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6">
        <div class="card stats-card" style="border-left-color: #6f42c1;">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-circle fs-3" style="background-color: #6f42c1; color: white;">
                            <i class="ri-play-circle-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Running</p>
                        <h4 class="mb-0" style="color: #6f42c1;">{{ number_format($stats['running_shares']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success rounded-circle fs-3">
                            <i class="ri-check-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Completed</p>
                        <h4 class="mb-0 text-success">{{ number_format($stats['completed_shares']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6">
        <div class="card stats-card danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-danger rounded-circle fs-3">
                            <i class="ri-close-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Failed</p>
                        <h4 class="mb-0 text-danger">{{ number_format($stats['failed_shares']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Statistics Row for Bought/Sold Shares -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card" style="border-left-color: #28a745;">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-circle fs-3" style="background-color: #28a745; color: white;">
                            <i class="ri-shopping-cart-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Bought Shares</p>
                        <h4 class="mb-0" style="color: #28a745;">{{ number_format($stats['bought_shares']) }}</h4>
                        <small class="text-muted">Purchased by users</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card" style="border-left-color: #fd7e14;">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-circle fs-3" style="background-color: #fd7e14; color: white;">
                            <i class="ri-sell-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Sold Shares</p>
                        <h4 class="mb-0" style="color: #fd7e14;">{{ number_format($stats['sold_shares']) }}</h4>
                        <small class="text-muted">Being sold by users</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-6">
        <div class="card stats-card" style="border-left-color: #17a2b8;">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h5 class="mb-0" style="color: #17a2b8;">{{ number_format($stats['total_amount'], 2) }}</h5>
                        <small class="text-muted">Total Amount (KSH)</small>
                    </div>
                    <div class="col-6">
                        <h5 class="mb-0" style="color: #17a2b8;">
                            {{ $stats['total_shares'] > 0 ? number_format(($stats['bought_shares'] / $stats['total_shares']) * 100, 1) : 0 }}%
                        </h5>
                        <small class="text-muted">Bought vs Total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="filter-section">
    <form method="GET" action="{{ route('admin.trade.view', $trade->id) }}" id="filterForm">
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label"><i class="ri-search-line me-1"></i>Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by ticket, user..." value="{{ request('search') }}">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="ri-shield-check-line me-1"></i>Status</label>
                <select name="status" class="form-select">
                    <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="pairing" {{ request('status') == 'pairing' ? 'selected' : '' }}>Pairing</option>
                    <option value="paired" {{ request('status') == 'paired' ? 'selected' : '' }}>Paired</option>
                    <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>Running</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="ri-exchange-line me-1"></i>Share Type</label>
                <select name="share_type" class="form-select">
                    <option value="all" {{ request('share_type') == 'all' || !request('share_type') ? 'selected' : '' }}>All Shares</option>
                    <option value="bought" {{ request('share_type') == 'bought' ? 'selected' : '' }}>Bought Shares</option>
                    <option value="sold" {{ request('share_type') == 'sold' ? 'selected' : '' }}>Sold Shares</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="ri-money-dollar-circle-line me-1"></i>Min Amount</label>
                <input type="number" name="amount_min" class="form-control" placeholder="Min" value="{{ request('amount_min') }}" step="0.01">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="ri-money-dollar-circle-line me-1"></i>Max Amount</label>
                <input type="number" name="amount_max" class="form-control" placeholder="Max" value="{{ request('amount_max') }}" step="0.01">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="ri-calendar-line me-1"></i>Date Range</label>
                <div class="input-group">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" style="font-size: 0.85rem;">
                    <span class="input-group-text" style="font-size: 0.75rem;">to</span>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" style="font-size: 0.85rem;">
                </div>
            </div>
        </div>
        
        <div class="row g-3 mt-2">
            <div class="col-md-2">
                <label class="form-label"><i class="ri-sort-asc me-1"></i>Sort By</label>
                <select name="sort_by" class="form-select">
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Created</option>
                    <option value="ticket_no" {{ request('sort_by') == 'ticket_no' ? 'selected' : '' }}>Ticket No</option>
                    <option value="amount" {{ request('sort_by') == 'amount' ? 'selected' : '' }}>Amount</option>
                    <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }}>Status</option>
                    <option value="period" {{ request('sort_by') == 'period' ? 'selected' : '' }}>Period</option>
                    <option value="share_will_get" {{ request('sort_by') == 'share_will_get' ? 'selected' : '' }}>Shares</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="ri-sort-desc me-1"></i>Sort Order</label>
                <select name="sort_order" class="form-select">
                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                </select>
            </div>
            
            <div class="col-md-8">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-light"><i class="ri-search-line me-1"></i>Filter</button>
                    <a href="{{ route('admin.trade.view', $trade->id) }}" class="btn btn-outline-light"><i class="ri-refresh-line me-1"></i>Reset</a>
                    <button type="button" id="exportBtn" class="btn btn-success"><i class="ri-download-line me-1"></i>Export</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">User Shares for {{ $trade->name }}</h5>
                    <small class="text-muted">Showing {{ $userShares->firstItem() ?? 0 }} to {{ $userShares->lastItem() ?? 0 }} of {{ $userShares->total() }} entries</small>
                </div>
            </div>
            <div class="card-body">
                @if($userShares->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Ticket No</th>
                                <th scope="col">User</th>
                                <th scope="col">Amount (KSH)</th>
                                <th scope="col">Period (Days)</th>
                                <th scope="col">Shares to Get</th>
                                <th scope="col">Status</th>
                                <th scope="col">Timer/Date</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userShares as $share)
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">{{ $share->ticket_no }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <div class="avatar-title bg-info-subtle text-info rounded">
                                                <i class="ri-user-line"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $share->user->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $share->user->username ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ number_format($share->amount, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $share->period }} days</span>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info">{{ number_format($share->share_will_get) }}</span>
                                </td>
                                <td>
                                    @php
                                        // Check if this is a running share (completed, sold, and in countdown phase)
                                        $isRunningShare = $share->status === 'completed' && 
                                                        $share->is_ready_to_sell == 0 && 
                                                        $share->selling_started_at && 
                                                        $share->pairedShares->where('is_paid', 1)->count() > 0;
                                    @endphp
                                    @if($isRunningShare)
                                        <span class="badge status-badge" style="background-color: #6f42c1; color: white;">Running</span>
                                    @else
                                        @switch($share->status)
                                            @case('pending')
                                                <span class="badge bg-warning status-badge">Pending</span>
                                                @break
                                            @case('pairing')
                                                <span class="badge bg-info status-badge">Pairing</span>
                                                @break
                                            @case('paired')
                                                <span class="badge bg-secondary status-badge">Paired</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success status-badge">Completed</span>
                                                @break
                                            @case('failed')
                                                <span class="badge bg-danger status-badge">Failed</span>
                                                @break
                                            @default
                                                <span class="badge bg-light status-badge">{{ $share->status }}</span>
                                        @endswitch
                                    @endif
                                </td>
                                <td>
                                    @php
                                        // Check if this is a running share (completed, sold, and in countdown phase)
                                        $isRunningShare = $share->status === 'completed' && 
                                                        $share->is_ready_to_sell == 0 && 
                                                        $share->selling_started_at && 
                                                        $share->pairedShares->where('is_paid', 1)->count() > 0;
                                    @endphp
                                    @if($isRunningShare && $share->selling_started_at)
                                        @php
                                            // For running shares, use selling_started_at + period for maturity calculation
                                            // Ensure selling_started_at is a Carbon instance
                                            $sellingStarted = $share->selling_started_at;
                                            if (is_string($sellingStarted)) {
                                                $sellingStarted = \Carbon\Carbon::parse($sellingStarted);
                                            }
                                            
                                            // Handle matured_at field
                                            $maturedAt = $share->matured_at;
                                            if ($maturedAt && is_string($maturedAt)) {
                                                $maturedAt = \Carbon\Carbon::parse($maturedAt);
                                            }
                                            
                                            $maturityDate = $maturedAt ? $maturedAt : $sellingStarted->addDays($share->period);
                                            $isOverdue = $maturityDate->isPast();
                                        @endphp
                                        <div class="countdown-timer" 
                                             data-target-date="{{ $maturityDate->toISOString() }}"
                                             data-share-id="{{ $share->id }}">
                                            @if($isOverdue)
                                                <span class="badge bg-danger">Overdue</span>
                                                <br><small class="text-danger">{{ $maturityDate->diffForHumans() }}</small>
                                            @else
                                                <span class="countdown-display fw-semibold text-primary" id="countdown-{{ $share->id }}">
                                                    <i class="ri-time-line me-1"></i>Loading...
                                                </span>
                                                <br><small class="text-muted">Until: {{ $maturityDate->format('M d, Y h:i A') }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">{{ $share->created_at->format('M d, Y') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $share->created_at->format('h:i A') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-info view-share" 
                                                data-id="{{ $share->id }}" 
                                                data-bs-toggle="tooltip" 
                                                title="View Details">
                                            <i class="ri-eye-fill"></i>
                                        </button>
                                        @if($share->payments->count() > 0)
                                        <button class="btn btn-sm btn-success view-payments" 
                                                data-id="{{ $share->id }}" 
                                                data-bs-toggle="tooltip" 
                                                title="View Payments">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <p class="text-muted mb-0">
                            Showing {{ $userShares->firstItem() ?? 0 }} to {{ $userShares->lastItem() ?? 0 }} of {{ $userShares->total() }} results
                        </p>
                    </div>
                    <div>
                        {{ $userShares->links() }}
                    </div>
                </div>
                @else
                <div class="text-center py-5">
                    <div class="avatar-xl mx-auto mb-4">
                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-1">
                            <i class="ri-search-line"></i>
                        </div>
                    </div>
                    <h5>No user shares found</h5>
                    <p class="text-muted">No user shares found for this trade with current filters.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-submit form on select change
    document.querySelectorAll('select[name="status"], select[name="sort_by"], select[name="sort_order"]').forEach(function(select) {
        select.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
    
    // View share details
    document.querySelectorAll('.view-share').forEach(function(button) {
        button.addEventListener('click', function() {
            const shareId = this.getAttribute('data-id');
            // You can implement a modal or redirect to detailed view
            Swal.fire({
                title: 'Share Details',
                text: `View detailed information for share ID: ${shareId}`,
                icon: 'info',
                confirmButtonText: 'OK'
            });
        });
    });
    
    // View payments
    document.querySelectorAll('.view-payments').forEach(function(button) {
        button.addEventListener('click', function() {
            const shareId = this.getAttribute('data-id');
            // You can implement payment details view
            Swal.fire({
                title: 'Payment Details',
                text: `View payment information for share ID: ${shareId}`,
                icon: 'info',
                confirmButtonText: 'OK'
            });
        });
    });
    
    // Initialize countdown timers
    initializeCountdownTimers();
    
    // Handle export functionality
    document.getElementById('exportBtn').addEventListener('click', function() {
        // Get current filter parameters
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        
        // Create export URL with current filters (you can implement this in controller)
        const exportUrl = `{{ route('admin.trade.view', $trade->id) }}?export=csv&${params.toString()}`;
        
        // Show loading state
        this.innerHTML = '<i class="ri-loader-line me-1"></i>Exporting...';
        this.disabled = true;
        
        // Create temporary link and trigger download
        const link = document.createElement('a');
        link.href = exportUrl;
        link.download = 'trade_{{ $trade->id }}_shares_export.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Reset button state after download
        setTimeout(() => {
            this.innerHTML = '<i class="ri-download-line me-1"></i>Export';
            this.disabled = false;
        }, 2000);
    });
});

// Countdown timer functionality
function initializeCountdownTimers() {
    const timers = document.querySelectorAll('.countdown-timer');
    
    timers.forEach(timer => {
        const targetDate = new Date(timer.dataset.targetDate);
        const shareId = timer.dataset.shareId;
        const displayElement = document.getElementById(`countdown-${shareId}`);
        
        if (displayElement && targetDate > new Date()) {
            updateCountdown(displayElement, targetDate, shareId);
            
            // Update every second
            setInterval(() => {
                updateCountdown(displayElement, targetDate, shareId);
            }, 1000);
        }
    });
}

function updateCountdown(element, targetDate, shareId) {
    const now = new Date();
    const timeLeft = targetDate - now;
    
    if (timeLeft <= 0) {
        element.innerHTML = '<i class="ri-time-line me-1"></i><span class="text-danger">Expired</span>';
        return;
    }
    
    const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
    const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
    
    let timeString = '';
    
    if (days > 0) {
        timeString = `${days}d ${hours}h ${minutes}m ${seconds}s`;
    } else if (hours > 0) {
        timeString = `${hours}h ${minutes}m ${seconds}s`;
    } else if (minutes > 0) {
        timeString = `${minutes}m ${seconds}s`;
    } else {
        timeString = `${seconds}s`;
    }
    
    // Color coding based on time left
    let colorClass = 'text-primary';
    const hoursLeft = timeLeft / (1000 * 60 * 60);
    
    if (hoursLeft <= 1) {
        colorClass = 'text-danger';
    } else if (hoursLeft <= 24) {
        colorClass = 'text-warning';
    } else if (hoursLeft <= 72) {
        colorClass = 'text-info';
    }
    
    element.innerHTML = `<i class="ri-time-line me-1"></i><span class="${colorClass}">${timeString}</span>`;
}

</script>
@endsection
