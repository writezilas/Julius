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
                <div class="card-header">
                    <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                </div>
                <div class="card-body">
                    @can('customer-update')
                        <!-- Editable User Information Form -->
                        <form action="{{ route('admin.user.update', $user->id) }}" method="post" id="user-info-form">
                            @csrf
                            @method('PUT')
                            <table class="table table-bordered">
                                <tr>
                                    <th width="200px">Name</th>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="name" value="{{ $user->name }}" required>
                                            <span class="input-group-text"><i class="ri-user-line"></i></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Username</th>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="{{ $user->username }}" readonly disabled>
                                            <input type="hidden" name="username" value="{{ $user->username }}">
                                            <span class="input-group-text"><i class="ri-at-line"></i></span>
                                        </div>
                                        <small class="text-muted">Username cannot be changed</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>
                                        <div class="input-group">
                                            <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                                            <span class="input-group-text"><i class="ri-mail-line"></i></span>
                                        </div>
                                        <small class="text-muted">Email must be unique</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>
                                        <div class="input-group">
                                            <input type="tel" class="form-control" name="phone" value="{{ $user->phone }}" required>
                                            <span class="input-group-text"><i class="ri-phone-line"></i></span>
                                        </div>
                                        <small class="text-muted">Phone number must be unique</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Referral Code</th>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="{{ $user->username }}" readonly disabled>
                                            <input type="hidden" name="refferal_code" value="{{ $user->username }}">
                                            <span class="input-group-text"><i class="ri-links-line"></i></span>
                                        </div>
                                        <small class="text-muted">Referral code always equals username</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Balance</th>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">Ksh</span>
                                            <input type="text" class="form-control" value="{{ number_format($user->balance ?? 0, 2) }}" readonly disabled>
                                            <input type="hidden" name="balance" value="{{ $user->balance ?? 0 }}">
                                            <span class="input-group-text"><i class="ri-wallet-line"></i></span>
                                        </div>
                                        <small class="text-muted">Balance cannot be modified from this panel</small>
                                    </td>
                                </tr>
                                @php
                                    $businessProfile = json_decode($user->business_profile, true) ?? [];
                                @endphp
                                <tr>
                                    <th>Mpesa Name</th>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="mpesa_name" value="{{ $businessProfile['mpesa_name'] ?? '' }}">
                                            <span class="input-group-text"><i class="ri-user-3-line"></i></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Mpesa Number</th>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="mpesa_no" value="{{ $businessProfile['mpesa_no'] ?? '' }}">
                                            <span class="input-group-text"><i class="ri-smartphone-line"></i></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Mpesa Till Number</th>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="mpesa_till_no" value="{{ $businessProfile['mpesa_till_no'] ?? '' }}">
                                            <span class="input-group-text"><i class="ri-store-line"></i></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Mpesa Till Name</th>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="mpesa_till_name" value="{{ $businessProfile['mpesa_till_name'] ?? '' }}">
                                            <span class="input-group-text"><i class="ri-store-2-line"></i></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <div class="status-info mb-2">
                                            {{ $user->status === 'active' ? 'Active' : ucfirst($user->status) }}
                                            @if($user->status === 'blocked')
                                                <br><small class="text-danger">
                                                    <i class="ri-lock-2-line me-1"></i>Permanently Blocked
                                                </small>
                                            @elseif($user->isSuspended())
                                                <br><small class="text-warning">
                                                    Suspended until: {{ $user->suspension_until->format('M d, Y H:i') }}
                                                    <br>Time remaining: <span id="suspension-countdown">{{ $user->suspension_until->diffForHumans() }}</span>
                                                </small>
                                            @elseif($user->status === 'active')
                                                <br><small class="text-success">
                                                    <i class="ri-check-circle-line me-1"></i>Account Active
                                                </small>
                                            @endif
                                        </div>
                                        <small class="text-muted">Status can be changed in the section below</small>
                                    </td>
                                </tr>
                            </table>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success me-2">
                                        <i class="ri-save-line me-2"></i>Save Changes
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                                        <i class="ri-refresh-line me-2"></i>Reset Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    @else
                        <!-- Read-only view for users without update permission -->
                        <table class="table table-bordered">
                            <tr>
                                <th width="200px">Name</th>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <th>Username</th>
                                <td>{{ $user->username }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>{{ $user->phone }}</td>
                            </tr>
                            <tr>
                                <th>Referral Code</th>
                                <td>{{ $user->refferal_code ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Balance</th>
                                <td>Ksh {{ number_format($user->balance ?? 0, 2) }}</td>
                            </tr>
                            @php
                                $businessProfile = json_decode($user->business_profile, true) ?? [];
                            @endphp
                            <tr>
                                <th>Mpesa Name</th>
                                <td>{{ $businessProfile['mpesa_name'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Mpesa Number</th>
                                <td>{{ $businessProfile['mpesa_no'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Mpesa Till Number</th>
                                <td>{{ $businessProfile['mpesa_till_no'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Mpesa Till Name</th>
                                <td>{{ $businessProfile['mpesa_till_name'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    {{ $user->status === 'active' ? 'Active' : ucfirst($user->status) }}
                                    @if($user->status === 'blocked')
                                        <br><small class="text-danger">
                                            <i class="ri-lock-2-line me-1"></i>Permanently Blocked
                                        </small>
                                    @elseif($user->isSuspended())
                                        <br><small class="text-warning">
                                            Suspended until: {{ $user->suspension_until->format('M d, Y H:i') }}
                                            <br>Time remaining: <span id="suspension-countdown">{{ $user->suspension_until->diffForHumans() }}</span>
                                        </small>
                                    @elseif($user->status === 'active')
                                        <br><small class="text-success">
                                            <i class="ri-check-circle-line me-1"></i>Account Active
                                        </small>
                                    @endif
                                </td>
                            </tr>
                        </table>
                        
                        <div class="alert alert-warning mt-3">
                            <i class="ri-information-line me-2"></i>
                            You don't have permission to edit user information. Contact your administrator for update permissions.
                        </div>
                    @endcan
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">User status update</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <form action="{{ route('user.status.update', $user->id) }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="form-group">
                                    <label>User status</label>
                                    <select class="form-control" id="userstatus" name="status">
                                        <option value="active" @selected($user->status == 'active')>Active</option>
                                        <option value="suspended" @selected($user->status == 'suspended')>Suspend</option>
                                        <option value="blocked" @selected($user->status == 'blocked')>Block</option>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-6 suspend" style="display:none;">
                                    <div class="form-group">
                                        <label>Suspension duration (hours)</label>
                                        <input type="number" class="form-control" name="suspension_duration" value="24" min="1" max="8760">
                                        <small class="text-muted">Duration in hours (max 1 year = 8760 hours)</small>
                                        @if($user->suspension_until)
                                            <br><span class="text-warning">
                                                Current suspension until: {{ $user->suspension_until->format('M d, Y H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6 block" style="display:none;">
                                    <div class="form-group">
                                        <label>Block time (hours)</label>
                                        <input type="number" class="form-control" name="time" value="1">
                                        @if($user->block_until)
                                            <span class="text-warning">
                                                Block until: {{ $user->block_until->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @can('customer-update')
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                                @endcan
                            </div>
                        </form>
                    </table>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

@endsection
@section('script')
<script>
    $('#userstatus').change(function(){
        $('.suspend, .block').hide();
        if($(this).val() === 'suspended'){
            $('.suspend').show();
        } else if($(this).val() === 'blocked'){
            $('.block').show();
        }
    })
    
    // Show appropriate fields on page load
    if($('#userstatus').val() == 'suspended'){
        $('.suspend').show();
    } else if($('#userstatus').val() == 'blocked'){
        $('.block').show();
    }
    
    // User information form validation and submission
    $('#user-info-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = new FormData(form[0]);
        
        // Disable submit button and show loading
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="ri-loader-2-line ri-spin me-2"></i>Saving...');
        
        // Submit form
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                toastr.success('User information updated successfully!');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while updating user information.';
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                }
                
                toastr.error(errorMessage);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Real-time validation feedback for editable fields only
    $('input[name="email"]').on('blur', function() {
        const email = $(this).val();
        const originalEmail = '{{ $user->email }}';
        
        if (email !== originalEmail && email.length > 0) {
            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailRegex.test(email)) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        }
    });
    
    $('input[name="phone"]').on('blur', function() {
        const phone = $(this).val();
        const originalPhone = '{{ $user->phone }}';
        
        if (phone !== originalPhone && phone.length > 0) {
            // Validate phone format (basic validation)
            if (phone.length >= 10) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        }
    });
    
    @if($user->isSuspended())
    // Update countdown every second for suspended users
    function updateCountdown() {
        const suspensionUntil = new Date('{{ $user->suspension_until->toISOString() }}');
        const now = new Date();
        const diff = suspensionUntil - now;
        
        if (diff <= 0) {
            $('#suspension-countdown').text('Expired (refresh page)');
            return;
        }
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        let countdownText = '';
        if (days > 0) countdownText += days + 'd ';
        if (hours > 0) countdownText += hours + 'h ';
        if (minutes > 0) countdownText += minutes + 'm ';
        countdownText += seconds + 's';
        
        $('#suspension-countdown').text(countdownText + ' remaining');
    }
    
    // Update immediately and then every second
    updateCountdown();
    setInterval(updateCountdown, 1000);
    @endif
</script>
@endsection
