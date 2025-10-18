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
                            <th>Actions</th>
                            @endcan
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($supports as $support)

                            <tr id="support-{{ $support->id }}" class="support-row">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $support->name }}</td>
                                <td>{{ $support->email }}</td>
                                <td>{{ $support->username }}</td>
                                <td>{{ $support->number }}</td>
                                <td>{{ $support->created_at->diffForHumans() }}</td>
                                <td>
                                    @if($support->admin_reply)
                                        <span class="badge bg-success">
                                            Replied
                                        </span>
                                    @elseif($support->status === 0)
                                        <span class="badge bg-info text-dark">
                                            Pending
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Closed
                                        </span>
                                    @endif
                                </td>
                                @can('support-view')
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary support-view-btn" data-support-id="{{ $support->id }}">
                                        <i class="ri-eye-line"></i> View
                                    </button>
                                    @if(!$support->admin_reply)
                                        <button type="button" class="btn btn-sm btn-success support-reply-btn" data-support-id="{{ $support->id }}">
                                            <i class="ri-reply-line"></i> Reply
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-success support-view-reply-btn" data-support-id="{{ $support->id }}">
                                            <i class="ri-mail-check-line"></i> View Reply
                                        </button>
                                    @endif
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
    
    <!-- Admin Reply Modal -->
    <div class="modal fade" id="supportReplyModal" tabindex="-1" aria-labelledby="supportReplyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="supportReplyModalLabel">Reply to Support Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="replyForm">
                    @csrf
                    <div class="modal-body">
                        <div id="supportReplyContent">
                            <!-- Support details will be loaded here -->
                        </div>
                        
                        <div class="mt-4">
                            <label for="adminReply" class="form-label">Your Reply <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="adminReply" name="admin_reply" rows="6" 
                                    placeholder="Type your response to the user's support request..." 
                                    required minlength="10" maxlength="2000"></textarea>
                            <div class="form-text">Minimum 10 characters, maximum 2000 characters</div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="sendReplyBtn">
                            <i class="ri-send-plane-fill"></i> Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Single Modal for All Support Requests -->
    <div class="modal" id="supportViewModal" tabindex="-1" role="dialog" aria-labelledby="supportViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="supportViewModalLabel">Support Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="supportModalContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
<style>
/* Highlight style for new support notifications */
.support-row.highlight {
    background-color: #fff3cd !important;
    border-left: 4px solid #ffc107;
    animation: highlight-fade 3s ease-in-out;
}

@keyframes highlight-fade {
    0% { background-color: #fff3cd; }
    100% { background-color: transparent; }
}

.support-row.highlight td {
    background-color: inherit;
}

/* Modal Styling - Light/Dark Mode Support */

/* Light Mode (Default) */
#supportViewModal .modal-content {
    background-color: #ffffff !important;
    color: #212529 !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 0.5rem;
}

#supportViewModal .modal-header {
    background-color: #ffffff !important;
    color: #212529 !important;
    border-bottom: 1px solid #dee2e6 !important;
}

#supportViewModal .modal-body {
    background-color: #ffffff !important;
    color: #212529 !important;
}

#supportViewModal .modal-footer {
    background-color: #ffffff !important;
    color: #212529 !important;
    border-top: 1px solid #dee2e6 !important;
}

#supportViewModal .modal-title {
    color: #212529 !important;
}

#supportViewModal .table {
    background-color: transparent !important;
    color: #212529 !important;
    margin-bottom: 0;
}

#supportViewModal .table td,
#supportViewModal .table th {
    background-color: transparent !important;
    color: #212529 !important;
    border-color: #dee2e6 !important;
    padding: 0.75rem;
    vertical-align: middle;
}

#supportViewModal .btn-close {
    filter: none !important;
}

/* Dark Mode Overrides */
[data-layout-mode="dark"] #supportViewModal .modal-content {
    background-color: #2a3042 !important;
    color: #ffffff !important;
    border-color: #3e4853 !important;
}

[data-layout-mode="dark"] #supportViewModal .modal-header {
    background-color: #2a3042 !important;
    color: #ffffff !important;
    border-bottom-color: #3e4853 !important;
}

[data-layout-mode="dark"] #supportViewModal .modal-body {
    background-color: #2a3042 !important;
    color: #ffffff !important;
}

[data-layout-mode="dark"] #supportViewModal .modal-footer {
    background-color: #2a3042 !important;
    color: #ffffff !important;
    border-top-color: #3e4853 !important;
}

[data-layout-mode="dark"] #supportViewModal .modal-title {
    color: #ffffff !important;
}

[data-layout-mode="dark"] #supportViewModal .table {
    color: #ffffff !important;
}

[data-layout-mode="dark"] #supportViewModal .table td,
[data-layout-mode="dark"] #supportViewModal .table th {
    color: #ffffff !important;
    border-color: #3e4853 !important;
}

[data-layout-mode="dark"] #supportViewModal .btn-close {
    filter: invert(1) !important;
}

/* Ensure backdrop is properly styled */
#supportViewModal.modal {
    --bs-modal-bg: transparent;
}

.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5) !important;
}

/* Reply Modal Styling */
#supportReplyModal .modal-content {
    background-color: #ffffff !important;
    color: #212529 !important;
    border: 1px solid #dee2e6 !important;
}

#supportReplyModal .modal-header {
    background-color: #ffffff !important;
    color: #212529 !important;
    border-bottom: 1px solid #dee2e6 !important;
}

#supportReplyModal .modal-body {
    background-color: #ffffff !important;
    color: #212529 !important;
}

#supportReplyModal .modal-footer {
    background-color: #ffffff !important;
    color: #212529 !important;
    border-top: 1px solid #dee2e6 !important;
}

#supportReplyModal .form-control {
    background-color: #ffffff !important;
    color: #212529 !important;
    border-color: #ced4da !important;
}

#supportReplyModal .form-control:focus {
    background-color: #ffffff !important;
    color: #212529 !important;
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

/* Dark Mode Overrides for Reply Modal */
[data-layout-mode="dark"] #supportReplyModal .modal-content {
    background-color: #2a3042 !important;
    color: #ffffff !important;
    border-color: #3e4853 !important;
}

[data-layout-mode="dark"] #supportReplyModal .modal-header {
    background-color: #2a3042 !important;
    color: #ffffff !important;
    border-bottom-color: #3e4853 !important;
}

[data-layout-mode="dark"] #supportReplyModal .modal-body {
    background-color: #2a3042 !important;
    color: #ffffff !important;
}

[data-layout-mode="dark"] #supportReplyModal .modal-footer {
    background-color: #2a3042 !important;
    color: #ffffff !important;
    border-top-color: #3e4853 !important;
}

[data-layout-mode="dark"] #supportReplyModal .form-control {
    background-color: #3e4853 !important;
    color: #ffffff !important;
    border-color: #4a5568 !important;
}

[data-layout-mode="dark"] #supportReplyModal .form-control:focus {
    background-color: #3e4853 !important;
    color: #ffffff !important;
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

[data-layout-mode="dark"] #supportReplyModal .btn-close {
    filter: invert(1) !important;
}

[data-layout-mode="dark"] #supportReplyModal .bg-light {
    background-color: #3e4853 !important;
    color: #ffffff !important;
}

[data-layout-mode="dark"] #supportReplyModal .bg-white {
    background-color: #2a3042 !important;
    color: #ffffff !important;
    border-color: #4a5568 !important;
}
</style>

<script>
$(document).ready(function() {
    
    // Support data for modal
    const supportData = @json($supports->keyBy('id'));
    
    // Handle anchor links for support notifications
    function handleSupportAnchor() {
        const hash = window.location.hash;
        if (hash && hash.startsWith('#support-')) {
            const supportId = hash.replace('#support-', '');
            const supportRow = document.getElementById('support-' + supportId);
            
            if (supportRow) {
                // Scroll to the support row
                supportRow.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                // Add highlight class
                supportRow.classList.add('highlight');
                
                // Remove highlight after animation
                setTimeout(() => {
                    supportRow.classList.remove('highlight');
                }, 3000);
                
                console.log('Support notification: Highlighted support request #' + supportId);
            }
        }
    }
    
    // Handle support view button clicks
    $('.support-view-btn').on('click', function() {
        const supportId = $(this).data('support-id');
        const support = supportData[supportId];
        
        if (support) {
            // Update modal title
            $('#supportViewModalLabel').text('Support request from ' + support.first_name);
            
            // Create modal content
            const modalContent = `
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td style="width: 30%"><b>Name</b></td>
                            <td>${support.first_name} ${support.last_name}</td>
                        </tr>
                        <tr>
                            <td style="width: 30%"><b>Email</b></td>
                            <td>${support.email}</td>
                        </tr>
                        <tr>
                            <td style="width: 30%"><b>Phone</b></td>
                            <td>${support.number || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td style="width: 30%"><b>Username</b></td>
                            <td>${support.username || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td style="width: 30%"><b>Created at</b></td>
                            <td>${new Date(support.created_at).toLocaleString()}</td>
                        </tr>
                        <tr>
                            <td style="width: 30%"><b>Status</b></td>
                            <td>
                                <span class="badge ${support.status === 0 ? 'bg-info text-dark' : 'bg-success'}">
                                    ${support.status === 0 ? 'Running' : 'Closed'}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 30%"><b>Message</b></td>
                            <td style="white-space: pre-wrap;">${support.message}</td>
                        </tr>
                        ${support.admin_reply ? `
                        <tr>
                            <td style="width: 30%"><b>Admin Reply</b></td>
                            <td style="white-space: pre-wrap;">${support.admin_reply}</td>
                        </tr>
                        <tr>
                            <td style="width: 30%"><b>Replied At</b></td>
                            <td>${support.replied_at ? new Date(support.replied_at).toLocaleString() : 'N/A'}</td>
                        </tr>` : ''}
                    </tbody>
                </table>
            `;
            
            // Update modal content
            $('#supportModalContent').html(modalContent);
            
            // Show modal
            const modal = new bootstrap.Modal('#supportViewModal');
            modal.show();
            
            // Ensure proper theming is applied
            setTimeout(() => {
                enforceModalTheming();
            }, 100);
        }
    });
    
    // Function to enforce modal theming
    function enforceModalTheming() {
        const modal = document.getElementById('supportViewModal');
        const isLightMode = !document.body.hasAttribute('data-layout-mode') || 
                           document.body.getAttribute('data-layout-mode') !== 'dark';
        
        if (modal) {
            const modalContent = modal.querySelector('.modal-content');
            const modalHeader = modal.querySelector('.modal-header');
            const modalBody = modal.querySelector('.modal-body');
            const modalFooter = modal.querySelector('.modal-footer');
            const modalTitle = modal.querySelector('.modal-title');
            const table = modal.querySelector('.table');
            const tableCells = modal.querySelectorAll('.table td, .table th');
            const closeBtn = modal.querySelector('.btn-close');
            
            if (isLightMode) {
                // Light mode styling
                if (modalContent) {
                    modalContent.style.backgroundColor = '#ffffff';
                    modalContent.style.color = '#212529';
                    modalContent.style.borderColor = '#dee2e6';
                }
                if (modalHeader) {
                    modalHeader.style.backgroundColor = '#ffffff';
                    modalHeader.style.color = '#212529';
                    modalHeader.style.borderBottomColor = '#dee2e6';
                }
                if (modalBody) {
                    modalBody.style.backgroundColor = '#ffffff';
                    modalBody.style.color = '#212529';
                }
                if (modalFooter) {
                    modalFooter.style.backgroundColor = '#ffffff';
                    modalFooter.style.color = '#212529';
                    modalFooter.style.borderTopColor = '#dee2e6';
                }
                if (modalTitle) {
                    modalTitle.style.color = '#212529';
                }
                if (table) {
                    table.style.color = '#212529';
                }
                tableCells.forEach(cell => {
                    cell.style.color = '#212529';
                    cell.style.borderColor = '#dee2e6';
                });
                if (closeBtn) {
                    closeBtn.style.filter = 'none';
                }
            } else {
                // Dark mode styling
                if (modalContent) {
                    modalContent.style.backgroundColor = '#2a3042';
                    modalContent.style.color = '#ffffff';
                    modalContent.style.borderColor = '#3e4853';
                }
                if (modalHeader) {
                    modalHeader.style.backgroundColor = '#2a3042';
                    modalHeader.style.color = '#ffffff';
                    modalHeader.style.borderBottomColor = '#3e4853';
                }
                if (modalBody) {
                    modalBody.style.backgroundColor = '#2a3042';
                    modalBody.style.color = '#ffffff';
                }
                if (modalFooter) {
                    modalFooter.style.backgroundColor = '#2a3042';
                    modalFooter.style.color = '#ffffff';
                    modalFooter.style.borderTopColor = '#3e4853';
                }
                if (modalTitle) {
                    modalTitle.style.color = '#ffffff';
                }
                if (table) {
                    table.style.color = '#ffffff';
                }
                tableCells.forEach(cell => {
                    cell.style.color = '#ffffff';
                    cell.style.borderColor = '#3e4853';
                });
                if (closeBtn) {
                    closeBtn.style.filter = 'invert(1)';
                }
            }
            
            console.log('Modal theming enforced - Light mode:', isLightMode);
        }
    }
    
    // Handle reply button clicks
    $('.support-reply-btn').on('click', function() {
        const supportId = $(this).data('support-id');
        const support = supportData[supportId];
        
        if (support) {
            // Update reply modal title
            $('#supportReplyModalLabel').text('Reply to ' + support.first_name + '\'s Support Request');
            
            // Create support details content for reply modal
            const replyContent = `
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-3">Support Request Details:</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td style="width: 25%"><strong>Name:</strong></td>
                            <td>${support.first_name} ${support.last_name}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>${support.email}</td>
                        </tr>
                        <tr>
                            <td><strong>Username:</strong></td>
                            <td>${support.username || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>${support.number || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Date:</strong></td>
                            <td>${new Date(support.created_at).toLocaleDateString()}</td>
                        </tr>
                    </table>
                    <div class="mt-3">
                        <strong>User's Message:</strong>
                        <div class="mt-2 p-2 border rounded bg-white" style="white-space: pre-wrap;">${support.message}</div>
                    </div>
                </div>
            `;
            
            // Update reply modal content
            $('#supportReplyContent').html(replyContent);
            
            // Clear previous reply content
            $('#adminReply').val('');
            
            // Store support ID in form
            $('#replyForm').attr('data-support-id', supportId);
            
            // Show reply modal
            const modal = new bootstrap.Modal('#supportReplyModal');
            modal.show();
        }
    });
    
    // Handle reply form submission
    $('#replyForm').on('submit', function(e) {
        e.preventDefault();
        
        const supportId = $(this).attr('data-support-id');
        const replyText = $('#adminReply').val().trim();
        const submitBtn = $('#sendReplyBtn');
        const originalBtnText = submitBtn.html();
        
        // Validation
        if (replyText.length < 10) {
            $('#adminReply').addClass('is-invalid');
            $('.invalid-feedback').text('Reply must be at least 10 characters long');
            return;
        }
        
        if (replyText.length > 2000) {
            $('#adminReply').addClass('is-invalid');
            $('.invalid-feedback').text('Reply must be less than 2000 characters');
            return;
        }
        
        // Clear validation styling
        $('#adminReply').removeClass('is-invalid');
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line"></i> Sending...');
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken
            }
        });
        
        $.post('{{ route("admin.support.reply", ":id") }}'.replace(':id', supportId), {
            admin_reply: replyText
        })
        .done(function(response) {
            if (response.success) {
                // Show success message
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message);
                } else {
                    alert(response.message);
                }
                
                // Close reply modal
                $('#supportReplyModal').modal('hide');
                
                // Refresh page to show updated data
                location.reload();
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to send reply');
                } else {
                    alert('Failed to send reply');
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
            
            let errorMsg = 'Network error occurred while sending reply';
            
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                // Validation errors
                const errors = xhr.responseJSON.errors;
                if (errors.admin_reply) {
                    errorMsg = errors.admin_reply[0];
                    $('#adminReply').addClass('is-invalid');
                    $('.invalid-feedback').text(errorMsg);
                }
            } else if (xhr.status === 403) {
                errorMsg = 'Access denied. You may not have permission to reply to support requests.';
            } else if (xhr.status === 404) {
                errorMsg = 'Support request not found.';
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
            // Restore button state
            submitBtn.prop('disabled', false).html(originalBtnText);
        });
    });
    
    // Handle view reply button clicks (same as view but highlights the reply)
    $('.support-view-reply-btn').on('click', function() {
        const supportId = $(this).data('support-id');
        $('.support-view-btn[data-support-id="' + supportId + '"]').click();
    });
    
    // Handle on page load
    handleSupportAnchor();
    
    // Handle when hash changes (if user clicks notification while already on page)
    window.addEventListener('hashchange', handleSupportAnchor);
    
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
