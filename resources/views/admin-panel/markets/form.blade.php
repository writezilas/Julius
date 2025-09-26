@extends('layouts.master')
@section('title') {{$pageTitle}} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') @lang('translation.dashboard') @endslot
@slot('title') {{$pageTitle}} @endslot
@endcomponent

<!-- Mobile-First Responsive Form Layout -->
<div class="row justify-content-center">
    <div class="col-12 col-lg-8 col-xl-6">
        <!-- Market Form Preview Card (Mobile-Friendly) -->
        @if(isset($market))
        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
            <i class="ri-information-line me-2"></i>
            <div>
                <strong>Editing Market:</strong> {{ $market->open_time }} - {{ $market->close_time }}
                <span class="badge {{ $market->is_active ? 'bg-success' : 'bg-danger' }} ms-2">
                    {{ $market->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-md-8">
                        <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                        @php
                            try {
                                $appTimezone = get_gs_value('app_timezone') ?? 'UTC';
                            } catch (Exception $e) {
                                $appTimezone = 'UTC';
                            }
                            $currentTime = now()->setTimezone($appTimezone)->format('H:i');
                        @endphp
                        <small class="text-muted d-block d-md-inline">
                            <i class="ri-time-zone-line me-1"></i>
                            Timezone: {{ $appTimezone }} (Current: {{ $currentTime }})
                        </small>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('admin.markets.index') }}" class="btn btn-outline-primary flex-fill flex-md-grow-0">
                                <i class="ri-arrow-left-line"></i> 
                                <span class="d-none d-sm-inline">All Markets</span>
                                <span class="d-sm-none">Back</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Enhanced Form with Better Mobile UX -->
                <form action="{{ isset($market) ? route('admin.markets.update', $market->id) : route('admin.markets.store') }}" 
                      method="post" 
                      id="marketForm" 
                      class="needs-validation" 
                      novalidate>
                    @csrf
                    @if(isset($market))
                    @method('put')
                    @endif
                    
                    <!-- Time Configuration Section -->
                    <div class="mb-4">
                        <h6 class="fw-semibold text-muted mb-3">
                            <i class="ri-time-line me-1"></i>
                            Market Schedule
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="form-floating mb-3">
                                    @php
                                        $defaultOpenTime = isset($market) ? $market->open_time : now()->setTimezone($appTimezone)->format('H:i');
                                    @endphp
                                    <input type="time" 
                                           class="form-control @error('open_time') is-invalid @enderror" 
                                           name="open_time" 
                                           value="{{ old('open_time', $defaultOpenTime) }}"
                                           id="openTime"
                                           required>
                                    <label for="openTime">
                                        <i class="ri-sun-line me-1"></i>
                                        Market Opens At
                                    </label>
                                    @error('open_time')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="ri-information-line me-1"></i>
                                        Time in {{ $appTimezone }} timezone
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-6">
                                <div class="form-floating mb-3">
                                    @php
                                        $defaultCloseTime = isset($market) ? $market->close_time : now()->setTimezone($appTimezone)->addHour()->format('H:i');
                                    @endphp
                                    <input type="time" 
                                           class="form-control @error('close_time') is-invalid @enderror" 
                                           name="close_time" 
                                           value="{{ old('close_time', $defaultCloseTime) }}"
                                           id="closeTime"
                                           required>
                                    <label for="closeTime">
                                        <i class="ri-moon-line me-1"></i>
                                        Market Closes At
                                    </label>
                                    @error('close_time')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="ri-information-line me-1"></i>
                                        Time in {{ $appTimezone }} timezone
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Market Duration Preview -->
                        <div class="alert alert-light border d-flex align-items-center" id="durationPreview">
                            <i class="ri-time-line text-primary me-2"></i>
                            <span id="durationText">Calculate market duration...</span>
                        </div>
                    </div>
                    
                    <!-- Market Status Section -->
                    <div class="mb-4">
                        <h6 class="fw-semibold text-muted mb-3">
                            <i class="ri-settings-line me-1"></i>
                            Market Configuration
                        </h6>
                        
                        <div class="card bg-light border-0">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm flex-shrink-0 me-3">
                                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                <i class="ri-toggle-line"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Market Status</h6>
                                            <p class="text-muted mb-0 small">
                                                Enable or disable market trading
                                            </p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="is_active" 
                                               value="1" 
                                               {{ old('is_active', isset($market) ? $market->is_active : true) ? 'checked' : '' }}
                                               id="marketActiveToggle">
                                        <label class="form-check-label" for="marketActiveToggle">
                                            <span class="badge bg-success" id="statusBadge" style="display: none;">Active</span>
                                            <span class="badge bg-danger" id="inactiveStatusBadge" style="display: none;">Inactive</span>
                                        </label>
                                    </div>
                                </div>
                                @error('is_active')
                                <div class="text-danger mt-2">
                                    <small><strong>{{ $message }}</strong></small>
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    @can('market-update')
                    <div class="d-flex gap-3 justify-content-end flex-column flex-sm-row">
                        <a href="{{ route('admin.markets.index') }}" class="btn btn-light">
                            <i class="ri-close-line me-1"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="ri-save-line me-1"></i>
                            <span class="submit-text">{{ isset($market) ? 'Update Market' : 'Create Market' }}</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                    @endcan
                </form>
            </div>
        </div>
        
        <!-- Help Card - Mobile Friendly -->
        <div class="card mt-4">
            <div class="card-body p-3">
                <h6 class="card-title mb-2">
                    <i class="ri-question-line text-primary me-1"></i>
                    Quick Help
                </h6>
                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-start">
                            <i class="ri-time-line text-muted me-2 mt-1"></i>
                            <small class="text-muted">
                                <strong>Market Hours:</strong> Set when trading is allowed
                            </small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-start">
                            <i class="ri-toggle-line text-muted me-2 mt-1"></i>
                            <small class="text-muted">
                                <strong>Status:</strong> Enable/disable market operations
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<!-- Custom Form Styles -->
<style>
    .form-floating > .form-control {
        height: calc(3.5rem + 2px);
    }
    
    .form-floating > label {
        padding: 1rem 0.75rem;
    }
    
    .alert-light {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    
    @media (max-width: 768px) {
        .form-floating > .form-control {
            height: calc(3rem + 2px);
        }
        
        .form-floating > label {
            padding: 0.75rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .avatar-sm {
            width: 2rem;
            height: 2rem;
        }
    }
    
    @media (max-width: 576px) {
        .col-12.col-lg-8.col-xl-6 {
            padding: 0 0.5rem;
        }
        
        .card {
            margin-bottom: 1rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
        }
    }
</style>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Mobile-friendly form enhancements
    const isMobile = window.innerWidth <= 768;
    
    // Form validation
    const form = document.getElementById('marketForm');
    const submitBtn = document.getElementById('submitBtn');
    const openTimeInput = document.getElementById('openTime');
    const closeTimeInput = document.getElementById('closeTime');
    const statusToggle = document.getElementById('marketActiveToggle');
    const statusBadge = document.getElementById('statusBadge');
    const inactiveStatusBadge = document.getElementById('inactiveStatusBadge');
    const durationText = document.getElementById('durationText');
    
    // Initialize status badge display
    function updateStatusBadge() {
        if (statusToggle.checked) {
            statusBadge.style.display = 'inline-block';
            inactiveStatusBadge.style.display = 'none';
        } else {
            statusBadge.style.display = 'none';
            inactiveStatusBadge.style.display = 'inline-block';
        }
    }
    
    // Calculate and display market duration
    function calculateDuration() {
        if (openTimeInput.value && closeTimeInput.value) {
            const openTime = new Date(`1970-01-01T${openTimeInput.value}:00`);
            const closeTime = new Date(`1970-01-01T${closeTimeInput.value}:00`);
            
            if (closeTime > openTime) {
                const diffMs = closeTime - openTime;
                const hours = Math.floor(diffMs / (1000 * 60 * 60));
                const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                
                let durationString = '';
                if (hours > 0) {
                    durationString += `${hours} hour${hours !== 1 ? 's' : ''}`;
                }
                if (minutes > 0) {
                    if (durationString) durationString += ' and ';
                    durationString += `${minutes} minute${minutes !== 1 ? 's' : ''}`;
                }
                
                durationText.textContent = `Market will be open for ${durationString}`;
                durationText.parentElement.classList.remove('alert-warning');
                durationText.parentElement.classList.add('alert-light');
            } else {
                durationText.textContent = 'Close time must be after open time';
                durationText.parentElement.classList.remove('alert-light');
                durationText.parentElement.classList.add('alert-warning');
            }
        } else {
            durationText.textContent = 'Select both open and close times to calculate duration';
            durationText.parentElement.classList.remove('alert-warning');
            durationText.parentElement.classList.add('alert-light');
        }
    }
    
    // Event listeners
    statusToggle.addEventListener('change', function() {
        updateStatusBadge();
        // Haptic feedback for mobile
        if ('vibrate' in navigator && isMobile) {
            navigator.vibrate(50);
        }
    });
    
    openTimeInput.addEventListener('change', calculateDuration);
    closeTimeInput.addEventListener('change', calculateDuration);
    
    // Form submission with enhanced UX
    form.addEventListener('submit', function(e) {
        // Show loading state
        const submitText = submitBtn.querySelector('.submit-text');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        submitBtn.disabled = true;
        submitText.style.display = 'none';
        spinner.classList.remove('d-none');
        
        // Validate times
        if (openTimeInput.value && closeTimeInput.value) {
            const openTime = new Date(`1970-01-01T${openTimeInput.value}:00`);
            const closeTime = new Date(`1970-01-01T${closeTimeInput.value}:00`);
            
            if (closeTime <= openTime) {
                e.preventDefault();
                
                // Reset button state
                submitBtn.disabled = false;
                submitText.style.display = 'inline';
                spinner.classList.add('d-none');
                
                // Silent validation error - no popup
                // showNotification('error', 'Close time must be after open time');
                closeTimeInput.focus();
                return false;
            }
        }
        
        // Silent form submission - no popup
        // showNotification('info', 'Submitting form...');
    });
    
    // Enhanced notification function
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
            
            switch(type) {
                case 'success':
                    toastr.success(message);
                    break;
                case 'error':
                    toastr.error(message);
                    break;
                case 'warning':
                    toastr.warning(message);
                    break;
                case 'info':
                    toastr.info(message);
                    break;
                default:
                    toastr.info(message);
            }
        } else {
            alert(message);
        }
    }
    
    // Initialize on page load
    updateStatusBadge();
    calculateDuration();
    
    // Auto-focus first input on desktop
    if (!isMobile) {
        openTimeInput.focus();
    }
    
    // Enhanced mobile form behavior
    if (isMobile) {
        // Smooth scroll to focused inputs
        $('input, textarea, select').on('focus', function() {
            setTimeout(() => {
                $(this)[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        });
        
        // Add touch-friendly behavior
        $('.form-control').on('touchstart', function() {
            $(this).addClass('focus');
        });
    }
    
    // Handle window resize
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Update mobile detection
            isMobile = window.innerWidth <= 768;
        }, 250);
    });
});
</script>
@endsection
