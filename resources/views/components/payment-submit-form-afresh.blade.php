{{-- Afresh Payment Submit Form - Modern Design --}}
{{-- Usage: @include('components.payment-submit-form-afresh', ['user' => $user, 'share' => $share, 'businessProfile' => $businessProfile, 'totalShare' => $totalShare, 'tradePrice' => $tradePrice, 'pairedIds' => $pairedIds]) --}}

@props(['user', 'share', 'businessProfile', 'totalShare', 'tradePrice', 'pairedIds' => []])

@php
    $modalId = "afreshPaymentModal" . $user->id . "-" . $share->id;
    $formId = "afreshPaymentForm" . $user->id;
    $totalAmount = $totalShare * $tradePrice;
@endphp

<!-- Afresh Payment Modal with Modern Design -->
<div class="modal fade afresh-payment-modal" id="{{ $modalId }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="afreshPaymentLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content afresh-modal-content">
            
            <!-- Modern Header with Glass Effect -->
            <div class="afresh-modal-header">
                <div class="header-background"></div>
                <div class="header-content">
                    <div class="payment-icon-container">
                        <div class="payment-icon-inner">
                            <i class="ri-secure-payment-fill"></i>
                        </div>
                        <div class="icon-glow"></div>
                    </div>
                    <div class="header-text">
                        <h4 class="modal-title" id="afreshPaymentLabel{{ $user->id }}">
                            Secure Payment Gateway
                        </h4>
                        <p class="modal-subtitle">Complete your transaction safely & securely</p>
                    </div>
                    <button type="button" class="afresh-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            </div>

            <form id="{{ $formId }}" action="{{ route('user.shares.payment') }}" method="POST" class="afresh-payment-form needs-validation" novalidate>
                @csrf
                
                <div class="afresh-modal-body">
                    <!-- Transaction Overview Card -->
                    <div class="transaction-overview">
                        <div class="overview-card">
                            <div class="card-shine"></div>
                            <div class="overview-content">
                                <div class="seller-profile">
                                    <div class="seller-avatar-wrapper">
                                        <div class="seller-avatar">
                                            <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                        <div class="avatar-ring"></div>
                                    </div>
                                    <div class="seller-info">
                                        <h5>{{ $user->name }}</h5>
                                        <span>@{{ $user->username }}</span>
                                        <div class="verified-badge">
                                            <i class="ri-verified-badge-fill"></i>
                                            Verified Seller
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="transaction-details">
                                    <div class="detail-grid">
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="ri-coins-fill"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Shares</span>
                                                <span class="detail-value">{{ number_format($totalShare) }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="detail-item">
                                            <div class="detail-icon">
                                                <i class="ri-price-tag-3-fill"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Price/Share</span>
                                                <span class="detail-value">{{ formatPrice($tradePrice) }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="detail-item total-amount">
                                            <div class="detail-icon">
                                                <i class="ri-money-dollar-circle-fill"></i>
                                            </div>
                                            <div class="detail-content">
                                                <span class="detail-label">Total Amount</span>
                                                <span class="detail-value">{{ formatPrice($totalAmount) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instructions Card -->
                    <div class="payment-instructions">
                        <div class="instruction-card">
                            <div class="instruction-header">
                                <div class="instruction-icon">
                                    <i class="ri-smartphone-fill"></i>
                                </div>
                                <div class="instruction-title">
                                    <h6>M-Pesa Payment Instructions</h6>
                                    <span>Send {{ formatPrice($totalAmount) }} to the number below</span>
                                </div>
                            </div>
                            
                            <div class="mpesa-details">
                                <div class="mpesa-number-container">
                                    <div class="mpesa-number">{{ $businessProfile->mpesa_no ?? 'Not Set' }}</div>
                                    <button type="button" class="copy-btn" onclick="copyMpesaNumber('{{ $businessProfile->mpesa_no ?? '' }}')">
                                        <i class="ri-file-copy-line"></i>
                                        <span>Copy</span>
                                    </button>
                                </div>
                                <div class="payment-steps">
                                    <div class="step">
                                        <span class="step-number">1</span>
                                        <span>Open M-Pesa</span>
                                    </div>
                                    <div class="step">
                                        <span class="step-number">2</span>
                                        <span>Send Money</span>
                                    </div>
                                    <div class="step">
                                        <span class="step-number">3</span>
                                        <span>Enter Details Below</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Fields Section -->
                    <div class="form-fields-container">
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

                        <div class="form-grid">
                            <!-- Seller Information Row -->
                            <div class="form-row seller-info-row">
                                <div class="form-group">
                                    <div class="input-container">
                                        <label class="input-label">
                                            <i class="ri-user-fill"></i>
                                            Seller M-Pesa Name
                                        </label>
                                        <input type="text" 
                                               class="form-input readonly" 
                                               id="afreshNameField{{ $user->id }}" 
                                               name="name" 
                                               value="{{ $businessProfile->mpesa_name ?? $user->name ?? 'Not Set' }}" 
                                               readonly>
                                        <div class="input-border"></div>
                                        @error('name')
                                        <div class="error-message">
                                            <i class="ri-error-warning-line"></i>{{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-container">
                                        <label class="input-label">
                                            <i class="ri-phone-fill"></i>
                                            Seller M-Pesa Number
                                        </label>
                                        <input type="text" 
                                               class="form-input readonly" 
                                               id="afreshPhoneField{{ $user->id }}" 
                                               name="number" 
                                               value="{{ $businessProfile->mpesa_no ?? 'Not Set' }}" 
                                               readonly>
                                        <div class="input-border"></div>
                                        <div class="input-help">
                                            <i class="ri-information-line"></i>
                                            Verified seller account
                                        </div>
                                        @error('number')
                                        <div class="error-message">
                                            <i class="ri-error-warning-line"></i>{{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Transaction Details Row -->
                            <div class="form-row transaction-row">
                                <div class="form-group transaction-id-group">
                                    <div class="input-container">
                                        <label class="input-label required">
                                            <i class="ri-receipt-fill"></i>
                                            M-Pesa Transaction ID
                                        </label>
                                        <input type="text" 
                                               class="form-input transaction-input" 
                                               id="afreshTxsIdField{{ $user->id }}" 
                                               name="txs_id" 
                                               value="{{ old('txs_id') }}" 
                                               placeholder="e.g., RGH1234567" 
                                               required>
                                        <div class="input-border"></div>
                                        <div class="input-help">
                                            <i class="ri-information-line"></i>
                                            Enter the confirmation code from your M-Pesa SMS
                                        </div>
                                        <div class="validation-message"></div>
                                        @error('txs_id')
                                        <div class="error-message">
                                            <i class="ri-error-warning-line"></i>{{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group amount-group">
                                    <div class="input-container">
                                        <label class="input-label">
                                            <i class="ri-money-dollar-circle-fill"></i>
                                            Amount
                                        </label>
                                        <input type="number" 
                                               class="form-input amount-input readonly" 
                                               id="afreshAmountField{{ $user->id }}" 
                                               name="amount" 
                                               value="{{ $totalAmount }}" 
                                               readonly>
                                        <div class="input-border"></div>
                                        <div class="amount-badge">KES</div>
                                        @error('amount')
                                        <div class="error-message">
                                            <i class="ri-error-warning-line"></i>{{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Notes Row -->
                            <div class="form-row notes-row">
                                <div class="form-group full-width">
                                    <div class="input-container">
                                        <label class="input-label">
                                            <i class="ri-chat-3-fill"></i>
                                            Additional Notes (Optional)
                                        </label>
                                        <textarea class="form-input notes-input" 
                                                  id="afreshNoteField{{ $user->id }}" 
                                                  name="note_by_sender" 
                                                  placeholder="Any additional information for the seller..."
                                                  rows="3">{{ old('note_by_sender') }}</textarea>
                                        <div class="input-border"></div>
                                        @error('note_by_sender')
                                        <div class="error-message">
                                            <i class="ri-error-warning-line"></i>{{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="security-notice">
                        <div class="security-icon">
                            <i class="ri-shield-check-fill"></i>
                        </div>
                        <div class="security-content">
                            <h6>Secure Transaction</h6>
                            <p>Your transaction details are encrypted and protected. Only share your transaction ID after completing the M-Pesa payment.</p>
                        </div>
                        <div class="security-badge">
                            <i class="ri-lock-fill"></i>
                            SSL Secured
                        </div>
                    </div>
                </div>
                
                <!-- Modern Footer with Action Buttons -->
                <div class="afresh-modal-footer">
                    <div class="footer-background"></div>
                    <div class="footer-content">
                        <button type="button" class="btn-secondary afresh-btn" data-bs-dismiss="modal">
                            <i class="ri-arrow-left-line"></i>
                            <span>Cancel</span>
                        </button>
                        
                        <button type="button" 
                                class="btn-primary afresh-btn submit-btn" 
                                id="afreshSubmitBtn{{ $user->id }}" 
                                onclick="submitAfreshPaymentForm('{{ $formId }}', this)">
                            <div class="btn-content">
                                <i class="ri-secure-payment-fill"></i>
                                <span>Submit Payment</span>
                            </div>
                            <div class="btn-loading d-none">
                                <div class="loading-spinner"></div>
                                <span>Processing...</span>
                            </div>
                            <div class="btn-ripple"></div>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Floating Success Animation (Hidden by default) -->
<div class="floating-success d-none" id="afreshSuccessAnimation{{ $user->id }}">
    <div class="success-circle">
        <i class="ri-check-fill"></i>
    </div>
    <div class="success-text">Payment Submitted!</div>
</div>

@push('scripts')
<script>
    /**
     * Copy M-Pesa number with enhanced feedback
     * @param {string} text - The M-Pesa number to copy
     */
    function copyMpesaNumber(text) {
        if (!text || text === 'Not Set') {
            showAfreshNotification('No M-Pesa number available', 'error');
            return;
        }
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function() {
                showCopySuccess();
                playAfreshSound('copy');
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    /**
     * Show copy success feedback
     */
    function showCopySuccess() {
        const btn = event.target.closest('.copy-btn');
        if (!btn) return;
        
        const icon = btn.querySelector('i');
        const text = btn.querySelector('span');
        
        // Store original content
        const originalIcon = icon.className;
        const originalText = text.textContent;
        
        // Update to success state
        icon.className = 'ri-check-fill';
        text.textContent = 'Copied!';
        btn.classList.add('copied');
        
        // Reset after delay
        setTimeout(function() {
            icon.className = originalIcon;
            text.textContent = originalText;
            btn.classList.remove('copied');
        }, 2500);
    }

    /**
     * Fallback copy method
     * @param {string} text - Text to copy
     */
    function fallbackCopy(text) {
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
                showCopySuccess();
                playAfreshSound('copy');
            } else {
                showAfreshNotification('Failed to copy', 'error');
            }
        } catch (err) {
            console.error('Copy failed: ', err);
            showAfreshNotification('Copy not supported', 'error');
        }
        
        document.body.removeChild(textArea);
    }
</script>
@endpush
