@extends('layouts.master')
@section('title') User Management @endsection

@section('css')
<link href="{{ URL::asset('assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.css') }}" rel="stylesheet">
<link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<style>
.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
.filter-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}
.user-actions .btn {
    margin: 2px;
}
.stats-card {
    border-left: 4px solid #007bff;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}
.stats-card.active { border-left-color: #28a745; }
.stats-card.blocked { border-left-color: #dc3545; }
.stats-card.suspended { border-left-color: #ffc107; }
</style>
@endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1') @lang('translation.dashboard') @endslot
    @slot('title') User Management @endslot
@endcomponent

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xxl-3 col-md-6">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-primary bg-gradient rounded">
                            <div class="avatar-title text-white fs-16">
                                <i class="ri-group-line"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">{{ $stats['total'] }}</h5>
                        <p class="text-muted mb-0">Total Users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xxl-3 col-md-6">
        <div class="card stats-card active">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-success bg-gradient rounded">
                            <div class="avatar-title text-white fs-16">
                                <i class="ri-check-line"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">{{ $stats['active'] }}</h5>
                        <p class="text-muted mb-0">Active Users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xxl-3 col-md-6">
        <div class="card stats-card suspended">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-warning bg-gradient rounded">
                            <div class="avatar-title text-white fs-16">
                                <i class="ri-pause-line"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">{{ $stats['suspended'] }}</h5>
                        <p class="text-muted mb-0">Suspended Users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xxl-3 col-md-6">
        <div class="card stats-card blocked">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-danger bg-gradient rounded">
                            <div class="avatar-title text-white fs-16">
                                <i class="ri-forbid-line"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1">{{ $stats['blocked'] }}</h5>
                        <p class="text-muted mb-0">Blocked Users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">User Management</h4>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-soft-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                            <i class="ri-filter-line me-1"></i> Filters
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Filters Section -->
            <div class="collapse" id="filtersCollapse">
                <div class="card-body border-bottom">
                    <form id="filters-form">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="status-filter">
                                    <option value="">All Users</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" placeholder="Name, username, email..." value="{{ request('search') }}" id="search-filter">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Registration Date</label>
                                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" id="date-from-filter">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">To</label>
                                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" id="date-to-filter">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="ri-search-line"></i>
                                    </button>
                                    <button type="button" class="btn btn-soft-danger btn-sm" id="clear-filters">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table id="users-table" class="table table-striped table-bordered dt-responsive nowrap align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th>SR No.</th>
                                <th>User Info</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Registration</th>
                                <th>Balance</th>
                                @can('customer-view')
                                <th>Actions</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-3">
                                                <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">{{ $user->name }}</h6>
                                                <p class="text-muted mb-0 fs-12">{{ $user->username }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p class="mb-1 fs-13">{{ $user->email }}</p>
                                            <p class="text-muted mb-0 fs-12">{{ $user->phone }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        @if($user->status == 'active')
                                            <span class="badge bg-success-subtle text-success status-badge">
                                                <i class="ri-check-line me-1"></i>Active
                                            </span>
                                        @elseif($user->status == 'suspended')
                                            <span class="badge bg-warning-subtle text-warning status-badge">
                                                <i class="ri-pause-line me-1"></i>Suspended
                                            </span>
                                            @if($user->suspension_until)
                                                <small class="text-muted d-block">Until: {{ $user->suspension_until->format('M d, Y H:i') }}</small>
                                            @endif
                                        @elseif($user->status == 'blocked')
                                            <span class="badge bg-danger-subtle text-danger status-badge">
                                                <i class="ri-forbid-line me-1"></i>Blocked
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary status-badge">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fs-13">{{ $user->created_at->format('M d, Y') }}</span>
                                        <small class="text-muted d-block">{{ $user->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-medium">KSh {{ number_format($user->balance ?? 0, 2) }}</span>
                                    </td>
                                    @can('customer-view')
                                    <td>
                                        <div class="user-actions">
                                            <a href="{{ route('user.single', $user->id) }}" class="btn btn-primary btn-sm">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            @can('customer-update')
                                                <button class="btn btn-soft-success btn-sm" onclick="changeStatus({{ $user->id }}, 'active')" 
                                                    {{ $user->status == 'active' ? 'disabled' : '' }}>
                                                    <i class="ri-check-line"></i>
                                                </button>
                                                <button class="btn btn-soft-warning btn-sm" onclick="suspendUser({{ $user->id }})">
                                                    <i class="ri-pause-line"></i>
                                                </button>
                                                <button class="btn btn-soft-danger btn-sm" onclick="blockUser({{ $user->id }})">
                                                    <i class="ri-forbid-line"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ri-group-line fs-1 text-muted mb-2"></i>
                                            <h6 class="text-muted">No users found</h6>
                                            <p class="text-muted fs-14">Try adjusting your search criteria</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="d-flex justify-content-end mt-3">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modals -->
@include('admin-panel.users.partials.suspend-modal')
@include('admin-panel.users.partials.block-modal')

@endsection

@section('script')
<script src="{{ URL::asset('assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#users-table').DataTable({
        "pageLength": 25,
        "responsive": true,
        "order": [[0, "asc"]],
        "columnDefs": [
            {
                "targets": [6], // Actions column
                "orderable": false
            }
        ]
    });
    
    // Filters functionality
    $('#filters-form').on('submit', function(e) {
        e.preventDefault();
        applyFilters();
    });
    
    $('#clear-filters').on('click', function() {
        $('#filters-form')[0].reset();
        window.location.href = '{{ route("admin.users.unified") }}';
    });
    
    // Real-time search
    $('#search-filter').on('keyup', debounce(function() {
        if($(this).val().length > 2 || $(this).val().length === 0) {
            applyFilters();
        }
    }, 500));
    
    // Status filter change
    $('#status-filter').on('change', function() {
        applyFilters();
    });
});

function applyFilters() {
    const formData = new FormData(document.getElementById('filters-form'));
    const params = new URLSearchParams(formData);
    window.location.href = '{{ route("admin.users.unified") }}?' + params.toString();
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function changeStatus(userId, status) {
    Swal.fire({
        title: 'Change User Status',
        text: `Are you sure you want to ${status === 'active' ? 'activate' : status} this user?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed!'
    }).then((result) => {
        if (result.isConfirmed) {
            updateUserStatus(userId, status);
        }
    });
}

function suspendUser(userId) {
    $('#suspendModal').modal('show');
    $('#suspend-user-id').val(userId);
}

function blockUser(userId) {
    $('#blockModal').modal('show');
    $('#block-user-id').val(userId);
}

function updateUserStatus(userId, status, duration = null, durationType = 'hours') {
    const data = {
        status: status,
        _token: '{{ csrf_token() }}'
    };
    
    if (status === 'suspended' && duration) {
        data.suspension_duration = duration;
    } else if (status === 'blocked' && duration) {
        data.time = duration;
    }
    
    $.ajax({
        url: '{{ url("admin/user/status/update") }}/' + userId,
        method: 'POST',
        data: data,
        success: function(response) {
            Swal.fire({
                title: 'Success!',
                text: 'User status updated successfully.',
                icon: 'success',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                location.reload();
            });
        },
        error: function(xhr, status, error) {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to update user status. Please try again.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        }
    });
}

// Handle suspend form submission
$('#suspend-form').on('submit', function(e) {
    e.preventDefault();
    const userId = $('#suspend-user-id').val();
    const duration = $('#suspend-duration').val();
    
    updateUserStatus(userId, 'suspended', duration);
    $('#suspendModal').modal('hide');
});

// Handle block form submission
$('#block-form').on('submit', function(e) {
    e.preventDefault();
    const userId = $('#block-user-id').val();
    const duration = $('#block-duration').val();
    
    updateUserStatus(userId, 'blocked', duration);
    $('#blockModal').modal('hide');
});
</script>
@endsection
