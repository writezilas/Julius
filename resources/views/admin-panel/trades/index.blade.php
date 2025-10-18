@extends('layouts.master')
@section('title') {{$pageTitle}} @endsection

@section('css')
<link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
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

.trade-status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.action-buttons .btn {
    margin-right: 5px;
    border-radius: 6px;
}

/* Better handling of large numbers in stats cards */
.stats-card h4 {
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1.2;
    word-break: break-all;
    overflow-wrap: break-word;
}

/* Responsive font sizing for mobile */
@media (max-width: 768px) {
    .stats-card h4 {
        font-size: 1.25rem;
    }
}

@media (max-width: 576px) {
    .stats-card h4 {
        font-size: 1.125rem;
    }
}

/* Better table responsiveness for amounts */
.table td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}

.table td:nth-child(3), /* Price column */
.table td:nth-child(4) { /* Buying Price column */
    font-size: 0.9rem;
    font-weight: 600;
}

@media (max-width: 768px) {
    .table td:nth-child(3),
    .table td:nth-child(4) {
        font-size: 0.8rem;
    }
}
</style>
@endsection

@section('content')
@component('components.breadcrumb')
@slot('li_1') @lang('translation.dashboard') @endslot
@slot('title') {{$pageTitle}} @endslot
@endcomponent

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary rounded-circle fs-3">
                            <i class="ri-shopping-bag-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Total Trades</p>
                        <h4 class="mb-0 text-wrap">{{ number_format($stats['total_trades']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stats-card success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success rounded-circle fs-3">
                            <i class="ri-check-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Active Trades</p>
                        <h4 class="mb-0 text-success">{{ number_format($stats['active_trades']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stats-card warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning rounded-circle fs-3">
                            <i class="ri-pause-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Inactive Trades</p>
                        <h4 class="mb-0 text-warning">{{ number_format($stats['inactive_trades']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stats-card info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info rounded-circle fs-3">
                            <i class="ri-pie-chart-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">Total Shares</p>
                        <h4 class="mb-0 text-info">{{ number_format($stats['total_user_shares']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Filters Section -->
<div class="filter-section">
    <form method="GET" action="{{ route('admin.trade.index') }}" id="filterForm">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label"><i class="ri-search-line me-1"></i>Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name, ID..." value="{{ request('search') }}">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="ri-shield-check-line me-1"></i>Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="ri-money-dollar-circle-line me-1"></i>Min Price</label>
                <input type="number" name="price_min" class="form-control" placeholder="Min" value="{{ request('price_min') }}" step="0.01">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="ri-money-dollar-circle-line me-1"></i>Max Price</label>
                <input type="number" name="price_max" class="form-control" placeholder="Max" value="{{ request('price_max') }}" step="0.01">
            </div>
            
            <div class="col-md-3">
                <label class="form-label"><i class="ri-calendar-line me-1"></i>Date Range</label>
                <div class="input-group">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    <span class="input-group-text">to</span>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
        </div>
        
        <div class="row g-3 mt-2">
            <div class="col-md-2">
                <label class="form-label"><i class="ri-stack-line me-1"></i>Min Quantity</label>
                <input type="number" name="quantity_min" class="form-control" placeholder="Min" value="{{ request('quantity_min') }}">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="ri-stack-line me-1"></i>Max Quantity</label>
                <input type="number" name="quantity_max" class="form-control" placeholder="Max" value="{{ request('quantity_max') }}">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="ri-sort-asc me-1"></i>Sort By</label>
                <select name="sort_by" class="form-select">
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Created</option>
                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                    <option value="price" {{ request('sort_by') == 'price' ? 'selected' : '' }}>Price</option>
                    <option value="quantity" {{ request('sort_by') == 'quantity' ? 'selected' : '' }}>Quantity</option>
                    <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }}>Status</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="ri-sort-desc me-1"></i>Sort Order</label>
                <select name="sort_order" class="form-select">
                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-light"><i class="ri-search-line me-1"></i>Filter</button>
                    <a href="{{ route('admin.trade.index') }}" class="btn btn-outline-light"><i class="ri-refresh-line me-1"></i>Reset</a>
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
                    <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                    <small class="text-muted">Showing {{ $trades->firstItem() ?? 0 }} to {{ $trades->lastItem() ?? 0 }} of {{ $trades->total() }} entries</small>
                </div>
                @can('trade-create')
                <a href="{{ route('admin.trade.create') }}" class="btn btn-primary">
                    <i class="ri-add-circle-fill me-1"></i>Create New Trade
                </a>
                @endcan
            </div>
            <div class="card-body">
                @if($trades->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Trade Name</th>
                                <th scope="col">Price (KSH)</th>
                                <th scope="col">Buying Price (KSH)</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">User Shares</th>
                                <th scope="col">Status</th>
                                <th scope="col">Created At</th>
                                @canAny(['trade-edit', 'trade-delete'])
                                <th scope="col">Actions</th>
                                @endcanAny
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trades as $trade)
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">#{{ $trade->id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <div class="avatar-title bg-primary-subtle text-primary rounded">
                                                <i class="ri-exchange-line"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $trade->name }}</h6>
                                            <small class="text-muted">{{ $trade->slug }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-success">{{ number_format($trade->price, 2) }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-info">{{ number_format($trade->buying_price, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ number_format($trade->quantity) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info">{{ $trade->user_shares_count ?? 0 }} shares</span>
                                </td>
                                <td>
                                    @if($trade->status == 1)
                                        <span class="badge bg-success trade-status-badge">Active</span>
                                    @else
                                        <span class="badge bg-danger trade-status-badge">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">{{ $trade->created_at->format('M d, Y') }}</span>
                                    <br>
                                    <small class="text-muted">{{ $trade->created_at->format('h:i A') }}</small>
                                </td>
                                @canAny(['trade-edit', 'trade-delete'])
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.trade.view', $trade->id) }}" 
                                           class="btn btn-sm btn-info" 
                                           data-bs-toggle="tooltip" 
                                           title="View Trade Details">
                                            <i class="ri-eye-fill"></i>
                                        </a>
                                        @can('trade-edit')
                                        <a href="{{ route('admin.trade.edit', $trade->id) }}" 
                                           class="btn btn-sm btn-success" 
                                           data-bs-toggle="tooltip" 
                                           title="Edit Trade">
                                            <i class="ri-edit-2-fill"></i>
                                        </a>
                                        @endcan
                                        @can('trade-delete')
                                        <button type="button" 
                                                class="btn btn-sm btn-danger delete-trade" 
                                                data-id="{{ $trade->id }}"
                                                data-name="{{ $trade->name }}"
                                                data-bs-toggle="tooltip" 
                                                title="Delete Trade">
                                            <i class="ri-delete-bin-5-line"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                                @endcanAny
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <p class="text-muted mb-0">
                            Showing {{ $trades->firstItem() ?? 0 }} to {{ $trades->lastItem() ?? 0 }} of {{ $trades->total() }} results
                        </p>
                    </div>
                    <div>
                        {{ $trades->links() }}
                    </div>
                </div>
                @else
                <div class="text-center py-5">
                    <div class="avatar-xl mx-auto mb-4">
                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-1">
                            <i class="ri-search-line"></i>
                        </div>
                    </div>
                    <h5>No trades found</h5>
                    <p class="text-muted">Try adjusting your filters or create a new trade.</p>
                    @can('trade-create')
                    <a href="{{ route('admin.trade.create') }}" class="btn btn-primary">
                        <i class="ri-add-circle-fill me-1"></i>Create First Trade
                    </a>
                    @endcan
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>

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
    
    // Handle delete confirmation
    document.querySelectorAll('.delete-trade').forEach(function(button) {
        button.addEventListener('click', function() {
            const tradeId = this.getAttribute('data-id');
            const tradeName = this.getAttribute('data-name');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `You want to delete the trade "${tradeName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create and submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('admin/trade/delete') }}/${tradeId}`;
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    
                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    
                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
    
    // Clear individual filter fields
    document.querySelectorAll('.clear-filter').forEach(function(button) {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            document.querySelector(`[name="${target}"]`).value = '';
            document.getElementById('filterForm').submit();
        });
    });
    
    // Handle export functionality
    document.getElementById('exportBtn').addEventListener('click', function() {
        // Get current filter parameters
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        
        // Create export URL with current filters
        const exportUrl = `{{ route('admin.trade.export') }}?${params.toString()}`;
        
        // Show loading state
        this.innerHTML = '<i class="ri-loader-line me-1"></i>Exporting...';
        this.disabled = true;
        
        // Create temporary link and trigger download
        const link = document.createElement('a');
        link.href = exportUrl;
        link.download = 'trades_export.csv';
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
</script>
@endsection
