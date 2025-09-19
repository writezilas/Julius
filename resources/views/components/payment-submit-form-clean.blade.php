{{-- Clean Payment Submit Form - User-Friendly Design --}}
{{-- Usage: @include('components.payment-submit-form-clean', ['user' => $user, 'share' => $share, 'businessProfile' => $businessProfile, 'totalShare' => $totalShare, 'tradePrice' => $tradePrice, 'pairedIds' => $pairedIds]) --}}

@props(['user', 'share', 'businessProfile', 'totalShare', 'tradePrice', 'pairedIds' => []])

@php
    $modalId = "cleanPaymentModal" . $user->id . "-" . $share->id;
    $formId = "cleanPaymentForm" . $user->id;
    $totalAmount = $totalShare * $tradePrice;
    
    // Get payment details to determine if it's Till or regular M-Pesa
    $fullBusinessProfile = json_decode($user->business_profile);
    $paymentDetails = \App\Helpers\PaymentHelper::getPaymentDetails($fullBusinessProfile, $user);
    
    // Check if using M-Pesa Till (has till number and name)
    $tillNumber = isset($fullBusinessProfile->mpesa_till_no) ? trim($fullBusinessProfile->mpesa_till_no) : '';
    $tillName = isset($fullBusinessProfile->mpesa_till_name) ? trim($fullBusinessProfile->mpesa_till_name) : '';
    $isUsingTill = !empty($tillNumber) && !empty($tillName);
    
    // Check if payment has expired for frontend validation
    $hasExpired = false;
    $pairedSharePairs = $share->pairedWithThis;
    if ($pairedSharePairs && $pairedSharePairs->isNotEmpty()) {
        $deadlineMinutes = $share->payment_deadline_minutes ?? 60;
        $pairedTime = $pairedSharePairs->first()->created_at;
        $expiryTime = \Carbon\Carbon::parse($pairedTime)->addMinutes($deadlineMinutes);
        $hasExpired = $expiryTime < now();
    }
@endphp

<!-- Clean Payment Modal -->
<div class="modal fade clean-payment-modal" id="{{ $modalId }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cleanPaymentLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content clean-modal-content">
            
            <!-- Simple Header -->
            <div class="modal-header clean-header">
                <div class="d-flex align-items-center">
                    <div class="header-icon me-3">
                        <i class="ri-secure-payment-line text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white mb-0" id="cleanPaymentLabel{{ $user->id }}">
                            Submit Payment Confirmation
                        </h5>
                        <small class="text-white-50">Complete your purchase of {{ number_format($totalShare) }} shares</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="{{ $formId }}" action="{{ route('user.shares.payment') }}" method="POST" class="clean-payment-form">
                @csrf
                
                <div class="modal-body clean-body">
                    
                    <!-- Payment Summary Card -->
                    <div class="payment-summary-card mb-4">
                        <h6 class="summary-title">
                            <i class="ri-receipt-line me-2"></i>Payment Summary
                        </h6>
                        
                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <div class="summary-item">
                                    <div class="summary-label">Shares</div>
                                    <div class="summary-value text-success">{{ number_format($totalShare) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="summary-item">
                                    <div class="summary-label">Price per Share</div>
                                    <div class="summary-value text-info">{{ formatPrice($tradePrice) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="summary-item">
                                    <div class="summary-label">Total Amount</div>
                                    <div class="summary-value text-primary">{{ formatPrice($totalAmount) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seller Information -->
                    <div class="seller-card mb-4">
                        <h6 class="card-title">
                            <i class="ri-user-line me-2"></i>Send Payment To
                        </h6>
                        
                        <div class="seller-info d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="seller-avatar me-3">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="seller-name">{{ $user->name }}</div>
                                    <div class="seller-username">{{ $user->username }}</div>
                                </div>
                            </div>
                            
                            <div class="mpesa-info">
                                <div class="mpesa-number-display">
                                    <span class="mpesa-number">{{ $paymentDetails['payment_number'] }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary copy-number-btn" 
                                            onclick="copyToClipboard('{{ $paymentDetails['payment_number'] }}', this)"
                                            data-bs-toggle="tooltip" title="Copy {{ $isUsingTill ? 'M-Pesa Till' : 'M-Pesa Number' }}">
                                        <i class="ri-file-copy-line"></i>
                                    </button>
                                </div>
                                <small class="text-muted">{{ $paymentDetails['payment_name'] }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="instructions-card mb-4">
                        <div class="alert alert-info alert-modern">
                            <div class="d-flex align-items-start">
                                <i class="ri-information-line me-3 mt-1"></i>
                                <div>
                                    <strong>How to pay:</strong><br>
                                    @if($isUsingTill)
                                        1. Send <strong>{{ formatPrice($totalAmount) }}</strong> to <strong>Mpesa Till:{{ $paymentDetails['payment_number'] }}</strong> via M-Pesa<br>
                                    @else
                                        1. Send <strong>{{ formatPrice($totalAmount) }}</strong> to <strong>Mpesa No:{{ $paymentDetails['payment_number'] }}</strong> via M-Pesa<br>
                                    @endif
                                    2. Enter the transaction ID from your M-Pesa confirmation SMS below<br>
                                    3. Click "Submit Payment" to complete your purchase
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
                    <input type="hidden" value="{{ $paymentDetails['payment_number'] }}" name="received_phone_no">
                    <input type="hidden" value="{{ $paymentDetails['payment_name'] }}" name="name">
                    <input type="hidden" value="{{ $paymentDetails['payment_number'] }}" name="number">
                    <input type="hidden" value="{{ $totalAmount }}" name="amount">

                    <!-- Form Fields -->
                    <div class="form-fields">
                        
                        <!-- Transaction ID Field -->
                        <div class="form-group mb-3">
                            <label class="form-label required">
                                <i class="ri-receipt-line me-1"></i>
                                M-Pesa Transaction ID
                            </label>
                            <input type="text" 
                                   class="form-control transaction-id-input" 
                                   id="cleanTxsIdField{{ $user->id }}" 
                                   name="txs_id" 
                                   value="{{ old('txs_id') }}" 
                                   placeholder="e.g., RGH1234567" 
                                   required>
                            <div class="form-text">
                                <i class="ri-information-line me-1"></i>
                                Copy the transaction ID from your M-Pesa confirmation SMS
                            </div>
                            @error('txs_id')
                            <div class="text-danger small mt-1">
                                <i class="ri-error-warning-line me-1"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>

                        <!-- Additional Notes -->
                        <div class="form-group mb-3">
                            <label class="form-label">
                                <i class="ri-chat-3-line me-1"></i>
                                Additional Notes (Optional)
                            </label>
                            <textarea class="form-control" 
                                      id="cleanNoteField{{ $user->id }}" 
                                      name="note_by_sender" 
                                      placeholder="Any additional information about your payment..."
                                      rows="3">{{ old('note_by_sender') }}</textarea>
                            @error('note_by_sender')
                            <div class="text-danger small mt-1">
                                <i class="ri-error-warning-line me-1"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Simple Footer -->
                <div class="modal-footer clean-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="button" 
                            class="btn btn-success submit-payment-btn" 
                            id="cleanSubmitBtn{{ $user->id }}" 
                            onclick="submitCleanPaymentForm('{{ $formId }}', this)">
                        <i class="ri-secure-payment-line me-1"></i>Submit Payment
                        <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Clean Modal Styles -->
<style>
/* Clean Payment Modal - Minimal and User-Friendly */
.clean-payment-modal .clean-modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.clean-payment-modal .clean-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    padding: 1rem 1.5rem;
}

.clean-payment-modal .header-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.clean-payment-modal .clean-body {
    padding: 1.5rem;
    background: #ffffff;
}

.clean-payment-modal .payment-summary-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1.25rem;
}

.clean-payment-modal .summary-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 0;
}

.clean-payment-modal .summary-item {
    text-align: center;
    padding: 0.75rem;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.clean-payment-modal .summary-label {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.clean-payment-modal .summary-value {
    font-size: 1rem;
    font-weight: 700;
}

.clean-payment-modal .seller-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 1.25rem;
}

.clean-payment-modal .card-title {
    color: #212529 !important;
    font-weight: 600;
    margin-bottom: 1rem;
    text-shadow: none !important;
    opacity: 1 !important;
    filter: none !important;
}

.clean-payment-modal .summary-title {
    color: #212529 !important;
    font-weight: 600;
    margin-bottom: 0;
    text-shadow: none !important;
    opacity: 1 !important;
    filter: none !important;
}

.clean-payment-modal .modal-title {
    color: #ffffff !important;
    font-weight: 600;
    text-shadow: none !important;
    opacity: 1 !important;
    filter: none !important;
}

.clean-payment-modal .seller-avatar {
    width: 50px;
    height: 50px;
    background: #28a745;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
}

.clean-payment-modal .seller-name {
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
}

.clean-payment-modal .seller-username {
    color: #6c757d;
    font-size: 0.875rem;
}

.clean-payment-modal .mpesa-number-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
}

.clean-payment-modal .mpesa-number {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    font-size: 1.1rem;
    color: #28a745;
    letter-spacing: 1px;
}

.clean-payment-modal .copy-number-btn {
    border: 1px solid #28a745;
    color: #28a745;
    padding: 0.25rem 0.5rem;
    transition: all 0.2s ease;
}

.clean-payment-modal .copy-number-btn:hover {
    background: #28a745;
    color: white;
}

.clean-payment-modal .copy-number-btn.copied {
    background: #28a745;
    color: white;
}

.clean-payment-modal .instructions-card {
    margin-bottom: 0;
}

.clean-payment-modal .alert-modern {
    border-left: 4px solid #17a2b8;
    background: #e7f3ff;
    border-radius: 8px;
    padding: 1rem;
}

.clean-payment-modal .form-group {
    margin-bottom: 0;
}

.clean-payment-modal .form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.clean-payment-modal .form-label.required::after {
    content: ' *';
    color: #dc3545;
}

.clean-payment-modal .transaction-id-input {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.clean-payment-modal .transaction-id-input:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.clean-payment-modal .transaction-id-input.is-valid {
    border-color: #28a745;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.94-.94 1.38 1.38'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.clean-payment-modal .transaction-id-input.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='%23dc3545' d='M6 0a6 6 0 1 1 0 12A6 6 0 0 1 6 0zM4.5 4.5l3 3m0-3l-3 3'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.clean-payment-modal .form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.2s ease;
}

.clean-payment-modal .form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.clean-payment-modal .clean-footer {
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}

.clean-payment-modal .submit-payment-btn {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.clean-payment-modal .submit-payment-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.clean-payment-modal .submit-payment-btn:disabled {
    opacity: 0.7;
    transform: none !important;
}

/* Copy Success Animation */
.copy-success-popup {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #28a745;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    z-index: 10000;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    animation: copySuccessPopup 2s ease-out forwards;
}

@keyframes copySuccessPopup {
    0% {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.8);
    }
    20% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
    80% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
    100% {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.8);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .clean-payment-modal .modal-lg {
        max-width: 95%;
    }
    
    .clean-payment-modal .clean-body {
        padding: 1rem;
    }
    
    .clean-payment-modal .seller-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .clean-payment-modal .mpesa-info {
        width: 100%;
    }
    
    .clean-payment-modal .mpesa-number-display {
        justify-content: space-between;
        width: 100%;
    }
}

@media (max-width: 576px) {
    .clean-payment-modal .modal-lg {
        max-width: 100%;
        margin: 0.5rem;
    }
    
    .clean-payment-modal .clean-header {
        padding: 1rem;
    }
    
    .clean-payment-modal .header-icon {
        width: 35px;
        height: 35px;
        font-size: 16px;
    }
    
    .clean-payment-modal .modal-title {
        font-size: 1.1rem;
    }
    
    .clean-payment-modal .seller-avatar {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .clean-payment-modal .clean-footer {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .clean-payment-modal .clean-footer .btn {
        width: 100%;
        justify-content: center;
    }
}

/* No Scroll Design - Fixed Height Container */
.clean-payment-modal .clean-body {
    max-height: 70vh;
    overflow-y: auto;
}

/* Custom Scrollbar */
.clean-payment-modal .clean-body::-webkit-scrollbar {
    width: 6px;
}

.clean-payment-modal .clean-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.clean-payment-modal .clean-body::-webkit-scrollbar-thumb {
    background: #28a745;
    border-radius: 3px;
}

.clean-payment-modal .clean-body::-webkit-scrollbar-thumb:hover {
    background: #1e7e34;
}

/* Firefox Scrollbar */
.clean-payment-modal .clean-body {
    scrollbar-width: thin;
    scrollbar-color: #28a745 #f1f1f1;
}
</style>

<!-- Clean Modal JavaScript -->
<script>
/**
 * Submit clean payment form with simple validation
 * @param {string} formId - The form ID
 * @param {HTMLElement} buttonElement - The submit button
 */
function submitCleanPaymentForm(formId, buttonElement) {
    console.log('Clean Payment Form Submission Started');
    
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
        
        // Remove previous validation classes
        txField.classList.remove('is-valid', 'is-invalid');
        
        if (!value) {
            alert('Please enter the M-Pesa Transaction ID. This field is required.');
            txField.focus();
            txField.classList.add('is-invalid');
            return false;
        }
        
        // Simple validation - allow letters, numbers, spaces, hyphens, underscores
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
    
    // Validate other required fields
    const requiredFields = form.querySelectorAll('[required]');
    let allValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value || !field.value.trim()) {
            field.classList.add('is-invalid');
            allValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!allValid) {
        alert('Please fill in all required fields.');
        return false;
    }
    
    // User confirmation - DISABLED
    // if (!confirm('Are you sure you want to submit this payment information? Please ensure all details are correct.')) {
    //     return false;
    // }
    
    // Show loading state
    const spinner = buttonElement.querySelector('.spinner-border');
    const originalText = buttonElement.innerHTML;
    
    buttonElement.disabled = true;
    buttonElement.innerHTML = '<i class="ri-loader-2-line me-1"></i>Submitting... <span class="spinner-border spinner-border-sm ms-2" role="status"></span>';
    
    // Stop the countdown timer immediately when payment is submitted
    const modalId = buttonElement.closest('.modal').id;
    const timerKey = modalId.replace('cleanPaymentModal', '');
    const timerId = 'payment-timer-' + timerKey;
    
    console.log('Stopping timer for payment submission:', { modalId, timerKey, timerId });
    
    // Call the timer stop function from the parent page
    if (window.stopPaymentTimer && typeof window.stopPaymentTimer === 'function') {
        window.stopPaymentTimer(timerKey, timerId);
    }
    
    // Submit form
    try {
        // Stop the timer BEFORE form submission for immediate UI feedback
        const modalId = buttonElement.closest('.modal').id;
        const timerKey = modalId.replace('cleanPaymentModal', '');
        const timerId = 'payment-timer-' + timerKey;
        
        console.log('Stopping timer before form submission:', { modalId, timerKey, timerId });
        
        // Call the timer stop function from the parent page immediately
        if (window.stopPaymentTimer && typeof window.stopPaymentTimer === 'function') {
            window.stopPaymentTimer(timerKey, timerId);
        }
        
        // Also hide the pay button and update UI immediately
        const payButtonRow = document.querySelector(`#payment-timer-${timerKey}`)?.closest('tr');
        if (payButtonRow) {
            const actionCell = payButtonRow.querySelector('td:last-child');
            if (actionCell) {
                actionCell.innerHTML = '<span class="badge bg-info-subtle text-info px-3 py-2"><i class="ri-time-line align-middle me-1"></i>Payment Submitted - Awaiting Confirmation</span>';
            }
        }
        
        form.submit();
        return true;
    } catch (error) {
        console.error('Form submission error:', error);
        alert('Error submitting form: ' + error.message);
        
        // Reset loading state
        buttonElement.disabled = false;
        buttonElement.innerHTML = originalText;
        if (spinner) {
            spinner.classList.add('d-none');
        }
        return false;
    }
}

/**
 * Copy text to clipboard with user feedback
 * @param {string} text - The text to copy
 * @param {HTMLElement} buttonElement - The copy button
 */
function copyToClipboard(text, buttonElement) {
    if (!text || text === 'Not Set') {
        alert('No M-Pesa number available to copy');
        return;
    }
    
    // Try modern clipboard API first
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showCopySuccess(buttonElement, text);
        }).catch(function(err) {
            console.error('Clipboard API failed:', err);
            fallbackCopy(text, buttonElement);
        });
    } else {
        // Fallback for older browsers
        fallbackCopy(text, buttonElement);
    }
}

/**
 * Fallback copy method for older browsers
 * @param {string} text - Text to copy
 * @param {HTMLElement} buttonElement - The copy button
 */
function fallbackCopy(text, buttonElement) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.opacity = "0";
    textArea.style.left = "-999999px";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess(buttonElement, text);
        } else {
            alert('Failed to copy - please manually copy: ' + text);
        }
    } catch (err) {
        console.error('Fallback copy failed:', err);
        alert('Copy not supported. M-Pesa number: ' + text);
    }
    
    document.body.removeChild(textArea);
}

/**
 * Show copy success feedback
 * @param {HTMLElement} buttonElement - The copy button
 * @param {string} text - The copied text
 */
function showCopySuccess(buttonElement, text) {
    // Update button state
    const icon = buttonElement.querySelector('i');
    const originalIcon = icon.className;
    
    // Change to success state
    icon.className = 'ri-check-fill';
    buttonElement.classList.add('copied');
    
    // Show success popup
    const popup = document.createElement('div');
    popup.className = 'copy-success-popup';
    popup.innerHTML = '<i class="ri-check-fill me-2"></i>M-Pesa number copied!';
    document.body.appendChild(popup);
    
    // Reset after 2 seconds
    setTimeout(function() {
        icon.className = originalIcon;
        buttonElement.classList.remove('copied');
        if (popup.parentNode) {
            popup.parentNode.removeChild(popup);
        }
    }, 2000);
}

// Real-time transaction ID validation
document.addEventListener('DOMContentLoaded', function() {
    // Setup validation for transaction ID fields
    document.querySelectorAll('.transaction-id-input').forEach(input => {
        input.addEventListener('input', function() {
            const value = this.value.trim();
            const pattern = /^[A-Za-z0-9\s\-_.]{4,30}$/;
            
            this.classList.remove('is-invalid', 'is-valid');
            
            if (value.length === 0) {
                return; // Don't validate empty field
            }
            
            if (pattern.test(value)) {
                this.classList.add('is-valid');
            } else if (value.length >= 3) {
                this.classList.add('is-invalid');
            }
        });
        
        // Clear validation on focus
        input.addEventListener('focus', function() {
            this.classList.remove('is-invalid');
        });
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
