@extends('layouts.master')
@section('title') {{$pageTitle}} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') @lang('translation.dashboard') @endslot
@slot('title') {{$pageTitle}} @endslot
@endcomponent

<!-- Mobile-First Responsive Layout -->
<div class="row">
    <div class="col-12">
        <!-- Market Summary Cards - Mobile Friendly -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card stats-card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                                    <i class="ri-stock-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-uppercase fw-medium text-muted text-truncate fs-6 mb-1">Total Markets</p>
                                <h4 class="mb-0">{{ $markets->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stats-card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                    <i class="ri-check-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-uppercase fw-medium text-muted text-truncate fs-6 mb-1">Active</p>
                                <h4 class="mb-0">{{ $markets->where('is_active', true)->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stats-card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-3">
                                    <i class="ri-pause-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-uppercase fw-medium text-muted text-truncate fs-6 mb-1">Inactive</p>
                                <h4 class="mb-0">{{ $markets->where('is_active', false)->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card stats-card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-3">
                                    <i class="ri-time-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-uppercase fw-medium text-muted text-truncate fs-6 mb-1">Open Now</p>
                                @php
                                    $openMarketsCount = 0;
                                    foreach($markets as $market) {
                                        try {
                                            if($market->isOpen()) $openMarketsCount++;
                                        } catch (Exception $e) {
                                            // Handle silently
                                        }
                                    }
                                @endphp
                                <h4 class="mb-0">{{ $openMarketsCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Market Table Card -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center g-3">
                    <div class="col-md-8 col-12">
                        <div>
                            <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                            @php
                                try {
                                    $appTimezone = get_gs_value('app_timezone') ?? 'UTC';
                                } catch (Exception $e) {
                                    $appTimezone = 'UTC';
                                }
                            @endphp
                            <small class="text-muted d-block d-md-inline">All times shown in {{ $appTimezone }} timezone</small>
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="d-flex gap-2 justify-content-end">
                            @can('market-create')
                            <a href="{{ route('admin.markets.create') }}" class="btn btn-primary flex-fill flex-md-grow-0">
                                <i class="ri-add-box-fill"></i> 
                                <span class="d-none d-sm-inline">New Market</span>
                                <span class="d-sm-none">New</span>
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Mobile-First Table with Responsive Design -->
                <div class="table-responsive">
                    <table id="marketsTable" class="table table-hover table-nowrap align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">#</th>
                                <th>Market Schedule</th>
                                <th class="text-center">Status</th>
                                <th class="d-none d-lg-table-cell">Created At</th>
                                @canAny(['market-edit', 'market-delete'])
                                <th class="text-center" style="width: 120px;">Actions</th>
                                @endcanAny
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($markets as $market)
                            <tr>
                                <td class="text-center fw-medium">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="ri-time-line text-muted me-2"></i>
                                            <span class="fw-medium">{{$market->open_time}} - {{$market->close_time}}</span>
                                            @php
                                                $isMarketOpen = false;
                                                try {
                                                    $isMarketOpen = $market->isOpen();
                                                } catch (Exception $e) {
                                                    $isMarketOpen = false;
                                                }
                                            @endphp
                                            @if($isMarketOpen)
                                                <span class="badge bg-success-subtle text-success ms-2 pulse-badge">
                                                    <i class="ri-live-line me-1"></i>Live
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted d-lg-none">
                                            <i class="ri-calendar-line me-1"></i>
                                            {{ \Carbon\Carbon::parse($market->created_at)->format('M d, Y') }}
                                        </small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @can('market-edit')
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input market-status-toggle" type="checkbox" 
                                               data-market-id="{{$market->id}}" 
                                               {{ $market->is_active ? 'checked' : '' }}
                                               id="marketToggle{{$market->id}}">
                                        <label class="form-check-label ms-2" for="marketToggle{{$market->id}}">
                                            <span class="badge {{ $market->is_active ? 'bg-success' : 'bg-danger' }}" 
                                                  id="statusBadge{{$market->id}}">
                                                {{ $market->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </label>
                                    </div>
                                    @else
                                    <span class="badge {{ $market->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $market->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    @endcan
                                </td>
                                <td class="d-none d-lg-table-cell text-muted">
                                    {{ \Carbon\Carbon::parse($market->created_at)->format('M d, Y H:i') }}
                                </td>
                                @canAny(['market-edit', 'market-delete'])
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        @can('market-edit')
                                        <a href="{{ route('admin.markets.edit', $market->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           data-bs-toggle="tooltip" 
                                           title="Edit Market">
                                            <i class="ri-edit-2-line"></i>
                                        </a>
                                        @endcan
                                        @can('market-delete')
                                        <a href="{{ route('admin.markets.delete', $market->id) }}" 
                                           class="btn btn-sm btn-outline-danger delete_two" 
                                           data-bs-toggle="tooltip" 
                                           title="Delete Market">
                                            <i class="ri-delete-bin-line"></i>
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                                @endcanAny
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ canAny(['market-edit', 'market-delete']) ? '5' : '4' }}" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="avatar-xl mx-auto mb-4">
                                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                <i class="ri-stock-line display-4"></i>
                                            </div>
                                        </div>
                                        <h5 class="mb-2">No Markets Found</h5>
                                        <p class="text-muted mb-4">Get started by creating your first market schedule.</p>
                                        @can('market-create')
                                        <a href="{{ route('admin.markets.create') }}" class="btn btn-primary">
                                            <i class="ri-add-box-fill me-1"></i>Create Market
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<!-- DataTables CSS with Responsive Extension -->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet" type="text/css" />

<!-- Custom Market Page Styles -->
<style>
    .pulse-badge {
        animation: pulse-success 2s infinite;
    }
    
    @keyframes pulse-success {
        0% { background-color: var(--bs-success-bg-subtle); }
        50% { background-color: var(--bs-success); color: white; }
        100% { background-color: var(--bs-success-bg-subtle); }
    }
    
    .stats-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .stats-card .card-body {
            padding: 1rem 0.75rem;
        }
        
        .stats-card .avatar-sm {
            width: 2rem;
            height: 2rem;
        }
        
        .stats-card h4 {
            font-size: 1.25rem;
        }
        
        .table-responsive {
            border-radius: 0.5rem;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05);
        }
        
        .form-switch .form-check-input {
            width: 2.5em;
            height: 1.25em;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
    
    @media (max-width: 576px) {
        .card-header .row {
            gap: 0.5rem;
        }
        
        .table td {
            padding: 0.5rem 0.25rem;
        }
        
        .empty-state {
            padding: 2rem 1rem;
        }
    }
</style>
@endsection

@section('script')
<!-- DataTables JS with Responsive Extension -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize responsive DataTable
    var isMobile = window.innerWidth <= 768;
    var table = $('#marketsTable').DataTable({
        responsive: true,
        pageLength: isMobile ? 5 : 10,
        lengthMenu: isMobile ? [[5, 10, 25], [5, 10, 25]] : [[10, 25, 50], [10, 25, 50]],
        columnDefs: [
            {
                targets: [0], // SR No column
                responsivePriority: 1,
                width: '60px'
            },
            {
                targets: [1], // Market Schedule column
                responsivePriority: 2
            },
            {
                targets: [2], // Status column
                responsivePriority: 3,
                width: '120px'
            },
            {
                targets: [-1], // Actions column
                responsivePriority: 4,
                orderable: false,
                width: '120px'
            }
        ],
        language: {
            search: '',
            searchPlaceholder: 'Search markets...',
            lengthMenu: '_MENU_ markets per page',
            info: 'Showing _START_ to _END_ of _TOTAL_ markets',
            infoEmpty: 'No markets available',
            infoFiltered: '(filtered from _MAX_ total markets)',
            paginate: {
                first: '<i class="ri-skip-back-line"></i>',
                last: '<i class="ri-skip-forward-line"></i>',
                next: '<i class="ri-arrow-right-s-line"></i>',
                previous: '<i class="ri-arrow-left-s-line"></i>'
            },
            emptyTable: 'No markets found'
        },
        dom: isMobile 
            ? '<"row"<"col-sm-12"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            : '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // CSRF Token handling
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    if (!csrfToken) {
        console.error('CSRF token not found!');
        return;
    }
    
    // Enhanced mobile-friendly toggle handling
    $(document).on('change', '.market-status-toggle', function() {
        const marketId = $(this).data('market-id');
        const isChecked = $(this).is(':checked');
        const toggle = $(this);
        const statusBadge = $(`#statusBadge${marketId}`);
        
        // Provide haptic feedback on mobile devices
        if ('vibrate' in navigator && isMobile) {
            navigator.vibrate(50);
        }
        
        // Add loading state with better UX
        toggle.prop('disabled', true);
        const originalParent = toggle.parent();
        originalParent.addClass('loading-switch');
        
        // Create loading overlay for better visual feedback
        const loadingOverlay = $('<div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.8); border-radius: 0.375rem; z-index: 10;"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        originalParent.css('position', 'relative').append(loadingOverlay);
        
        // AJAX request with enhanced error handling
        $.ajax({
            url: `/admin/market/toggle-status/${marketId}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                _token: csrfToken
            },
            timeout: 10000, // 10 second timeout
            success: function(response) {
                if (response.success) {
                    // Update badge with animation
                    if (response.is_active) {
                        statusBadge.removeClass('bg-danger').addClass('bg-success').text('Active');
                    } else {
                        statusBadge.removeClass('bg-success').addClass('bg-danger').text('Inactive');
                    }
                    
                    // Silent success - no notification popup
                    // showNotification('success', response.message || 'Market status updated successfully');
                    
                    // Update stats cards if they exist
                    updateStatsCards();
                } else {
                    // Revert checkbox state on failure
                    toggle.prop('checked', !isChecked);
                    // Silent error - no notification popup
                    // showNotification('error', response.message || 'Failed to update market status');
                }
            },
            error: function(xhr, status, error) {
                // Revert checkbox state on error
                toggle.prop('checked', !isChecked);
                
                let errorMessage = 'Network error. Please check your connection and try again.';
                if (xhr.status === 403) {
                    errorMessage = 'You do not have permission to perform this action.';
                } else if (xhr.status === 422) {
                    errorMessage = 'Invalid request. Please refresh the page and try again.';
                } else if (xhr.status >= 500) {
                    errorMessage = 'Server error. Please try again later.';
                }
                
                // Silent error - no notification popup
                // showNotification('error', errorMessage);
            },
            complete: function() {
                // Remove loading state
                toggle.prop('disabled', false);
                originalParent.removeClass('loading-switch');
                loadingOverlay.remove();
            }
        });
    });
    
    // Enhanced notification system
    function showNotification(type, message) {
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                closeButton: true,
                debug: false,
                newestOnTop: true,
                progressBar: true,
                positionClass: isMobile ? 'toast-top-center' : 'toast-top-right',
                preventDuplicates: true,
                showDuration: '300',
                hideDuration: '1000',
                timeOut: '4000',
                extendedTimeOut: '1000',
                showEasing: 'swing',
                hideEasing: 'linear',
                showMethod: 'fadeIn',
                hideMethod: 'fadeOut'
            };
            
            if (type === 'success') {
                toastr.success(message);
            } else {
                toastr.error(message);
            }
        } else {
            // Fallback to native alert for better mobile support
            alert(message);
        }
    }
    
    // Function to update stats cards dynamically
    function updateStatsCards() {
        // This would typically make another AJAX call to get updated stats
        // For now, we'll just refresh the current stats from the table
        const totalMarkets = table.rows().count();
        const visibleRows = table.rows({search: 'applied'}).data();
        
        // Update the stats if the elements exist
        // This is a simplified version - in a real app you might want to make an API call
    }
    
    // Handle window resize for responsive behavior
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            const newIsMobile = window.innerWidth <= 768;
            if (newIsMobile !== isMobile) {
                // Reinitialize table with new mobile settings if needed
                isMobile = newIsMobile;
                table.page.len(isMobile ? 5 : 10).draw();
            }
        }, 250);
    });
    
    // Add smooth scrolling for mobile navigation
    if (isMobile) {
        $('html, body').css({
            'scroll-behavior': 'smooth'
        });
    }
    
    // Enhanced search functionality for mobile
    $('.dataTables_filter input').addClass('form-control form-control-sm').attr('placeholder', 'Search markets...');
    
    // Custom mobile-friendly pagination
    if (isMobile) {
        $('.dataTables_paginate').addClass('d-flex justify-content-center');
    }
});
</script>
@endsection
