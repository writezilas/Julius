{{-- Compact Afresh Payment Submit Form - Streamlined Design for bought-share-view --}}
{{-- Usage: @include('components.payment-submit-form-compact', ['user' => $user, 'share' => $share, 'businessProfile' => $businessProfile, 'totalShare' => $totalShare, 'tradePrice' => $tradePrice, 'pairedIds' => $pairedIds]) --}}

@props(['user', 'share', 'businessProfile', 'totalShare', 'tradePrice', 'pairedIds' => []])

@php
    $modalId = "compactPaymentModal" . $user->id . "-" . $share->id;
    $formId = "compactPaymentForm" . $user->id;
    $totalAmount = $totalShare * $tradePrice;
@endphp

<!-- Compact Payment Modal with Reduced Size -->
<div class="modal fade compact-payment-modal" id="{{ $modalId }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="compactPaymentLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content compact-modal-content">
            
            <!-- Compact Header -->
            <div class="compact-modal-header">
                <div class="d-flex align-items-center">
                    <div class="payment-icon me-2">
                        <i class="ri-secure-payment-fill text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="compactPaymentLabel{{ $user->id }}">
                            Quick Payment
                        </h5>
                        <small class="text-muted">{{ formatPrice($totalAmount) }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="{{ $formId }}" action="{{ route('user.shares.payment') }}" method="POST" class="compact-payment-form needs-validation" novalidate>
                @csrf
                
                <div class="compact-modal-body">
                    <!-- Seller Info - Single Line -->
                    <div class="seller-info-compact mb-3">
                        <div class="d-flex align-items-center bg-light rounded p-2">
                            <div class="seller-avatar-sm me-2">
                                <span class="text-primary fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                            <div class="flex-grow-1">
                                <small class="fw-semibold d-block">{{ $user->name }}</small>
                                <small class="text-muted">{{ $businessProfile->mpesa_no ?? 'Not Set' }}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyMpesaNumber('{{ $businessProfile->mpesa_no ?? '' }}')">
                                <i class="ri-file-copy-line"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Transaction Details - Compact Grid -->
                    <div class="transaction-summary-compact mb-3">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="text-center p-2 bg-success-subtle rounded">
                                    <small class="text-muted d-block">Shares</small>
                                    <strong class="text-success">{{ number_format($totalShare) }}</strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-2 bg-info-subtle rounded">
                                    <small class="text-muted d-block">Price</small>
                                    <strong class="text-info">{{ formatPrice($tradePrice) }}</strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-2 bg-primary-subtle rounded">
                                    <small class="text-muted d-block">Total</small>
                                    <strong class="text-primary">{{ formatPrice($totalAmount) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Fields -->
                    <input type="hidden" value="{{ $share->pairedWithThis && $share->pairedWithThis->isNotEmpty() ? $share->pairedWithThis->first()->user_share_id : '' }}" name="user_share_id">
                    @if(is_array($pairedIds) && count($pairedIds) > 0)
                        @foreach($pairedIds as $pairedId)
                        <input type="hidden" value="{{ $pairedId }}" name="user_share_pair_ids[]">
                        @endforeach
                    @endif
                    <input type="hidden" value="{{ $user->id }}" name="receiver_id">
                    <input type="hidden" value="{{ auth()->user()->id }}" name="sender_id">
                    <input type="hidden" value="{{ $businessProfile->mpesa_no ?? '' }}" name="received_phone_no">
                    <input type="hidden" value="{{ $businessProfile->mpesa_name ?? $user->name ?? 'Not Set' }}" name="name">
                    <input type="hidden" value="{{ $businessProfile->mpesa_no ?? 'Not Set' }}" name="number">
                    <input type="hidden" value="{{ $totalAmount }}" name="amount">

                    <!-- Form Fields - Compact Layout -->
                    <div class="form-fields-compact">
                        <!-- Transaction ID - Primary Field -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="ri-receipt-line me-1 text-primary"></i>
                                M-Pesa Transaction ID <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-sm transaction-input" 
                                   id="compactTxsIdField{{ $user->id }}" 
                                   name="txs_id" 
                                   value="{{ old('txs_id') }}" 
                                   placeholder="e.g., RGH1234567" 
                                   required>
                            <div class="form-text">
                                <i class="ri-information-line"></i>
                                Enter the confirmation code from your M-Pesa SMS
                            </div>
                            @error('txs_id')
                            <div class="text-danger small">
                                <i class="ri-error-warning-line"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>

                        <!-- Notes - Optional and Compact -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="ri-chat-3-line me-1 text-secondary"></i>
                                Additional Notes (Optional)
                            </label>
                            <textarea class="form-control form-control-sm" 
                                      id="compactNoteField{{ $user->id }}" 
                                      name="note_by_sender" 
                                      placeholder="Any additional information..."
                                      rows="2">{{ old('note_by_sender') }}</textarea>
                            @error('note_by_sender')
                            <div class="text-danger small">
                                <i class="ri-error-warning-line"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Quick Payment Instructions -->
                    <div class="payment-instructions-compact">
                        <div class="alert alert-primary alert-sm p-2 mb-0">
                            <div class="d-flex align-items-center">
                                <i class="ri-smartphone-line me-2"></i>
                                <div class="flex-grow-1">
                                    <small>
                                        <strong>Quick Steps:</strong>
                                        Send {{ formatPrice($totalAmount) }} to {{ $businessProfile->mpesa_no ?? 'Not Set' }} via M-Pesa, then enter the transaction ID above.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Compact Footer -->
                <div class="compact-modal-footer">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cancel
                        </button>
                        <button type="button" 
                                class="btn btn-sm btn-primary submit-btn" 
                                id="compactSubmitBtn{{ $user->id }}" 
                                onclick="submitCompactPaymentForm('{{ $formId }}', this)">
                            <i class="ri-secure-payment-line me-1"></i>Submit Payment
                            <span class="spinner-border spinner-border-sm ms-1 d-none" role="status"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Compact Payment Modal Styles */
.compact-payment-modal .compact-modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    max-height: 90vh;
    overflow: hidden;
}

.compact-payment-modal .compact-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 16px;
    border-radius: 12px 12px 0 0;
}

.compact-payment-modal .compact-modal-body {
    padding: 16px;
    max-height: 60vh;
    overflow-y: auto;
}

.compact-payment-modal .compact-modal-footer {
    background: #f8f9fa;
    padding: 12px 16px;
    border-top: 1px solid #dee2e6;
}

.compact-payment-modal .seller-avatar-sm {
    width: 32px;
    height: 32px;
    background: #e3f2fd;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.compact-payment-modal .payment-icon {
    width: 36px;
    height: 36px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.compact-payment-modal .transaction-input:valid {
    border-color: #28a745;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.94-.94 1.38 1.38'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.compact-payment-modal .alert-sm {
    padding: 8px 12px;
    font-size: 0.875rem;
}

.compact-payment-modal .form-control-sm {
    border-radius: 6px;
    font-size: 0.875rem;
}

.compact-payment-modal .btn-sm {
    padding: 6px 12px;
    font-size: 0.875rem;
    border-radius: 6px;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .compact-payment-modal .modal-dialog {
        max-width: 95% !important;
        margin: 1rem auto;
    }
    
    .compact-payment-modal .transaction-summary-compact .col-4 {
        flex: 0 0 33.333333%;
    }
    
    .compact-payment-modal .compact-modal-body {
        padding: 12px;
        max-height: 70vh;
    }
}

/* Animation for smooth interactions */
.compact-payment-modal .btn:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.compact-payment-modal .seller-info-compact:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}
</style>
@endpush

@push('scripts')
<script>
/**
 * Submit compact payment form with validation
 * @param {string} formId - The form ID
 * @param {HTMLElement} buttonElement - The submit button
 */
function submitCompactPaymentForm(formId, buttonElement) {
    console.log('🚀 Compact Payment Form Submission Started');
    
    // Find form
    const form = document.getElementById(formId);
    if (!form) {
        alert('Form not found. Please refresh and try again.');
        return false;
    }
    
    // Validate transaction ID
    const txField = form.querySelector('[name="txs_id"]');
    if (txField) {
        const value = txField.value.trim();
        if (!value) {
            alert('Please enter the M-Pesa Transaction ID. This field is required.');
            txField.focus();
            txField.classList.add('is-invalid');
            return false;
        }
        
        // Validate format
        const pattern = /^[A-Za-z0-9\s\-_.]{4,30}$/;
        if (!pattern.test(value)) {
            alert('Invalid M-Pesa Transaction ID format. Please use 4-30 characters (letters, numbers, spaces, hyphens allowed)');
            txField.focus();
            txField.classList.add('is-invalid');
            return false;
        } else {
            txField.classList.add('is-valid');
        }
    }
    
    // User confirmation - DISABLED
    // if (!confirm('Are you sure you want to submit this payment information?')) {
    //     return false;
    // }
    
    // Show loading state
    const spinner = buttonElement.querySelector('.spinner-border');
    buttonElement.disabled = true;
    if (spinner) {
        spinner.classList.remove('d-none');
    }
    
    // Submit form
    try {
        form.submit();
        return true;
    } catch (error) {
        console.error('Form submission error:', error);
        alert('Error submitting form: ' + error.message);
        
        // Reset loading state
        buttonElement.disabled = false;
        if (spinner) {
            spinner.classList.add('d-none');
        }
        return false;
    }
}

/**
 * Copy M-Pesa number functionality
 * @param {string} text - The M-Pesa number to copy
 */
function copyMpesaNumber(text) {
    if (!text || text === 'Not Set') {
        alert('No M-Pesa number available');
        return;
    }
    
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showCopySuccessCompact();
        }).catch(function(err) {
            fallbackCopyCompact(text);
        });
    } else {
        fallbackCopyCompact(text);
    }
}

/**
 * Show copy success feedback for compact modal
 */
function showCopySuccessCompact() {
    const btn = event.target.closest('button');
    if (!btn) return;
    
    const icon = btn.querySelector('i');
    const originalIcon = icon.className;
    
    // Update to success state
    icon.className = 'ri-check-fill';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-outline-primary');
    
    // Show toast-like feedback
    const toast = document.createElement('div');
    toast.className = 'position-fixed top-50 start-50 translate-middle bg-success text-white px-3 py-2 rounded';
    toast.style.zIndex = '9999';
    toast.innerHTML = '<i class="ri-check-fill me-1"></i>Copied to clipboard!';
    document.body.appendChild(toast);
    
    // Reset after delay
    setTimeout(function() {
        icon.className = originalIcon;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 2000);
}

/**
 * Fallback copy method
 * @param {string} text - Text to copy
 */
function fallbackCopyCompact(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.opacity = "0";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccessCompact();
        } else {
            alert('Failed to copy - please try again');
        }
    } catch (err) {
        alert('Copy not supported in this browser');
    }
    
    document.body.removeChild(textArea);
}
</script>
@endpush
