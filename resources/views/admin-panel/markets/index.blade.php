@extends('layouts.master')
@section('title') {{$pageTitle}} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') @lang('translation.dashboard') @endslot
@slot('title') {{$pageTitle}} @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div>
                    <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                    @php
                        try {
                            $appTimezone = get_gs_value('app_timezone') ?? 'UTC';
                        } catch (Exception $e) {
                            $appTimezone = 'UTC';
                        }
                    @endphp
                    <small class="text-muted">All times shown in {{ $appTimezone }} timezone</small>
                </div>
                @can('market-create')
                <a href="{{ route('admin.markets.create') }}" class="btn btn-primary">
                    <i class="ri-add-box-fill"></i> New
                </a>
                @endcan
            </div>
            <div class="card-body">
                <table id="alternative-pagination" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>SR No.</th>
                            <th>Open Time</th>
                            <th>Close Time</th>
                            <th>Status</th>
                            <th>Created At</th>
                            @canAny(['market-edit', 'market-delete'])
                            <th>Action</th>
                            @endcanAny
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($markets as $market)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{$market->open_time}} 
                                @php
                                    $isMarketOpen = false;
                                    try {
                                        $isMarketOpen = $market->isOpen();
                                    } catch (Exception $e) {
                                        // Silently handle any timezone or other errors
                                        $isMarketOpen = false;
                                    }
                                @endphp
                                @if($isMarketOpen)
                                    <span class="badge bg-success">Open Now</span>
                                @endif
                            </td>
                            <td>{{$market->close_time}}</td>
                            <td>
                                @can('market-edit')
                                <div class="form-check form-switch">
                                    <input class="form-check-input market-status-toggle" type="checkbox" 
                                           data-market-id="{{$market->id}}" 
                                           {{ $market->is_active ? 'checked' : '' }}
                                           id="marketToggle{{$market->id}}">
                                    <label class="form-check-label" for="marketToggle{{$market->id}}">
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
                            <td>{{\Carbon\Carbon::parse($market->created_at)}}</td>
                            @canAny(['market-edit', 'market-delete'])
                            <td>
                                @can('market-edit')
                                <a href="{{ route('admin.markets.edit', $market->id) }}" class="btn btn-sm btn-soft-success">
                                    <i class="ri-edit-2-fill"></i>
                                </a>
                                @endcan
                                @can('market-delete')
                                <a href="{{ route('admin.markets.delete', $market->id) }}" class="btn btn-sm btn-soft-danger delete_two">
                                    <i class="ri-delete-bin-5-line"></i>
                                </a>
                                @endcan
                            </td>
                            @endcanAny
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!--end col-->
</div>

@endsection

@section('css')
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('script')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    console.log('Market toggle script loaded');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Document ready state:', document.readyState);
    
    // Initialize DataTable first
    var table = $('#alternative-pagination').DataTable({
        "pagingType": "full_numbers"
    });
    
    // Check if CSRF token exists
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    if (!csrfToken) {
        console.error('CSRF token not found!');
        console.error('Meta tags found:', $('meta').length);
        $('meta').each(function(i, meta) {
            console.log('Meta tag ' + i + ':', $(meta).attr('name'), '=', $(meta).attr('content'));
        });
        return;
    }
    
    console.log('CSRF token found:', csrfToken.substring(0, 10) + '...');
    console.log('Toggle elements found:', $('.market-status-toggle').length);
    
    // Use event delegation to handle toggle switches (works with DataTable)
    $(document).on('change', '.market-status-toggle', function() {
        const marketId = $(this).data('market-id');
        const isChecked = $(this).is(':checked');
        const toggle = $(this);
        const statusBadge = $(`#statusBadge${marketId}`);
        
        console.log('Toggle clicked for market', marketId, 'New state:', isChecked);
        
        // Show loading state
        toggle.prop('disabled', true);
        
        // Make AJAX request to toggle status
        $.ajax({
            url: `/admin/market/toggle-status/${marketId}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: {
                _token: csrfToken
            },
            beforeSend: function(xhr) {
                console.log('Sending AJAX request to:', `/admin/market/toggle-status/${marketId}`);
            },
            success: function(response) {
                console.log('AJAX response:', response);
                if (response.success) {
                    // Update badge based on new status
                    if (response.is_active) {
                        statusBadge.removeClass('bg-danger').addClass('bg-success').text('Active');
                    } else {
                        statusBadge.removeClass('bg-success').addClass('bg-danger').text('Inactive');
                    }
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                } else {
                    // Revert checkbox state on failure
                    toggle.prop('checked', !isChecked);
                    if (typeof toastr !== 'undefined') {
                        toastr.error(response.message || 'Failed to update market status');
                    } else {
                        alert(response.message || 'Failed to update market status');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('=== AJAX ERROR DETAILS ===');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response Status Code:', xhr.status);
                console.error('Response Text:', xhr.responseText);
                console.error('Response Headers:', xhr.getAllResponseHeaders());
                console.error('Request URL was:', `/admin/market/toggle-status/${marketId}`);
                console.error('CSRF Token used:', csrfToken);
                console.error('========================');
                
                // Revert checkbox state on error
                toggle.prop('checked', !isChecked);
                
                let errorMessage = 'An error occurred while updating market status';
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        errorMessage = errorResponse.message;
                    }
                } catch (e) {
                    // Use default error message
                }
                
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage);
                } else {
                    alert(errorMessage);
                }
            },
            complete: function() {
                // Re-enable toggle
                toggle.prop('disabled', false);
                console.log('AJAX request completed');
            }
        });
    });
});
</script>
@endsection
