@extends('layouts.master')
@section('title') {{$pageTitle}}  @endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') @lang('translation.dashboard') @endslot
        @slot('title') {{$pageTitle}} @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                    <div class="d-flex align-items-center">
                        <span class="me-3 fw-bold text-dark">Support Form:</span>
                        <div class="form-check form-switch me-2">
                            <input class="form-check-input" type="checkbox" id="supportFormToggle" 
                                   {{ $supportFormEnabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="supportFormToggle">
                                <span id="toggleStatus" class="badge fs-6 px-3 py-2 {{ $supportFormEnabled ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                    {{ $supportFormEnabled ? 'ENABLED' : 'DISABLED' }}
                                </span>
                            </label>
                        </div>
                        <a href="{{ route('admin.supportFormSettings') }}" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="ri-settings-3-line"></i> Advanced Settings
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="alternative-pagination" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                        <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Phone no</th>
                            <th>Created at</th>
                            <th>Status</th>
                            @can('support-view')
                            <th>View</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($supports as $support)

                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $support->name }}</td>
                                <td>{{ $support->email }}</td>
                                <td>{{ $support->username }}</td>
                                <td>{{ $support->number }}</td>
                                <td>{{ $support->created_at->diffForHumans() }}</td>
                                <td>
                                    @if($support->status === 0)
                                        <span class="badge bg-info text-dark">
                                            Running
                                        </span>
                                    @else
                                        <span class="badge bg-success ">
                                            Closed
                                        </span>
                                    @endif
                                </td>
                                @can('support-view')
                                <td>
                                
                                   <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#supportModal{{ $support->id }}">
                                       View
                                   </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="supportModal{{ $support->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title" id="exampleModalLabel">
                                                        Support request from {{ $support->first_name }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-bordered">
                                                        <tbody>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Name</b>
                                                                </td>
                                                                <td>{{ $support->name }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Email</b>
                                                                </td>
                                                                <td>{{ $support->email }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Phone</b>
                                                                </td>
                                                                <td>{{ $support->number }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Username</b>
                                                                </td>
                                                                <td>{{ $support->username }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Created at</b>
                                                                </td>
                                                                <td>{{ $support->created_at->diffForHumans() }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Status</b>
                                                                </td>
                                                                <td>
                                                                    @if($support->status === 0)
                                                                        <span class="badge bg-info text-dark">
                                                                            Running
                                                                        </span>
                                                                    @else
                                                                        <span class="badge bg-success ">
                                                                            Closed
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Message</b>
                                                                </td>
                                                                <td>{{ $support->message }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%"><b>Email</b></td>
                                                                <td>{{ $support->email }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                @endcan
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
@section('script')
<script>
$(document).ready(function() {
    // Ensure CSRF token is available
    if (!window.Laravel) {
        window.Laravel = {};
    }
    window.Laravel.csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Handle support form toggle
    $('#supportFormToggle').on('change', function() {
        const toggleElement = $(this);
        const enabled = toggleElement.is(':checked') ? 1 : 0;
        const toggleStatus = $('#toggleStatus');
        const originalChecked = !enabled; // Store original state for rollback
        
        // Show loading state
        toggleStatus.removeClass('bg-success bg-danger text-white')
                   .addClass('bg-warning text-dark')
                   .text('UPDATING...');
        
        // Disable toggle during request
        toggleElement.prop('disabled', true);
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken
            }
        });
        
        $.post('{{ route("admin.support.toggleForm") }}', {
            enabled: enabled
        })
        .done(function(response) {
            if (response.success) {
                // Update status badge with better colors
                toggleStatus.removeClass('bg-warning text-dark');
                if (enabled) {
                    toggleStatus.addClass('bg-success text-white').text('ENABLED');
                } else {
                    toggleStatus.addClass('bg-danger text-white').text('DISABLED');
                }
                
                // Show success message
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message);
                } else {
                    alert(response.message);
                }
            } else {
                // Rollback toggle state
                toggleElement.prop('checked', originalChecked);
                toggleStatus.removeClass('bg-warning text-dark')
                           .addClass(originalChecked ? 'bg-success text-white' : 'bg-danger text-white')
                           .text(originalChecked ? 'ENABLED' : 'DISABLED');
                           
                const errorMsg = 'Failed to update support form setting';
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMsg);
                } else {
                    alert(errorMsg);
                }
            }
        })
        .fail(function(xhr, status, error) {
            console.error('AJAX Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            
            // Rollback toggle state
            toggleElement.prop('checked', originalChecked);
            toggleStatus.removeClass('bg-warning text-dark')
                       .addClass(originalChecked ? 'bg-success text-white' : 'bg-danger text-white')
                       .text(originalChecked ? 'ENABLED' : 'DISABLED');
            
            let errorMsg = 'Network error occurred while updating support form setting';
            
            // Provide more specific error messages
            if (xhr.status === 403) {
                errorMsg = 'Access denied. You may not have permission to perform this action.';
            } else if (xhr.status === 404) {
                errorMsg = 'Service endpoint not found. Please contact administrator.';
            } else if (xhr.status === 419) {
                errorMsg = 'Session expired. Please refresh the page and try again.';
            } else if (xhr.status === 500) {
                errorMsg = 'Server error occurred. Please try again or contact administrator.';
            }
            
            if (typeof toastr !== 'undefined') {
                toastr.error(errorMsg);
            } else {
                alert(errorMsg);
            }
        })
        .always(function() {
            // Re-enable toggle
            toggleElement.prop('disabled', false);
        });
    });
});
</script>
@endsection
