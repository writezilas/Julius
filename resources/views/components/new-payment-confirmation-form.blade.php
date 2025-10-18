{{-- New Payment Confirmation Form - Clean, Responsive, Light/Dark Mode Compatible --}}
{{-- Usage: @include('components.new-payment-confirmation-form', ['modalId' => $modalId, 'shares' => $shares, 'pricePerShare' => $pricePerShare, 'totalAmount' => $totalAmount, 'recipientName' => $recipientName, 'recipientUsername' => $recipientUsername, 'recipientAvatar' => $recipientAvatar, 'mpesaNumber' => $mpesaNumber, 'csrfToken' => $csrfToken, 'submitUrl' => $submitUrl]) --}}

@props([
    'modalId', 
    'shares', 
    'pricePerShare', 
    'totalAmount', 
    'recipientName', 
    'recipientUsername', 
    'recipientAvatar', 
    'mpesaNumber', 
    'csrfToken', 
    'submitUrl'
])

<!-- New Payment Confirmation Modal -->
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content new-payment-confirmation-modal">
            
            <!-- Header -->
            <div class="new-confirmation-header">
                <div class="header-content">
                    <div class="header-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c1.51 0 2.93.37 4.18 1.03"/>
                        </svg>
                    </div>
                    <div class="header-text">
                        <h4 class="modal-title" id="{{ $modalId }}Label">
                            Confirm Payment Details
                        </h4>
                        <p class="modal-subtitle">
                            Review your purchase of <span id="modal-shares-count">{{ $shares }}</span> shares
                        </p>
                    </div>
                </div>
                <button type="button" class="new-close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="new-confirmation-body">
                
                <!-- Payment Summary -->
                <div class="summary-section">
                    <h5 class="section-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10,9 9,9 8,9"/>
                        </svg>
                        Payment Summary
                    </h5>
                    
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="summary-label">Shares</div>
                            <div class="summary-value shares-value" id="summary-shares">{{ $shares }}</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Price per Share</div>
                            <div class="summary-value price-value" id="summary-price">{{ $pricePerShare }}</div>
                        </div>
                        <div class="summary-item total-item">
                            <div class="summary-label">Total Amount</div>
                            <div class="summary-value total-value" id="summary-total">{{ $totalAmount }}</div>
                        </div>
                    </div>
                </div>

                <!-- Send Payment To Section -->
                <div class="recipient-section">
                    <h5 class="section-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Send Payment To
                    </h5>
                    
                    <div class="recipient-card">
                        <div class="recipient-info">
                            <div class="recipient-avatar">
                                <img src="{{ $recipientAvatar }}" 
                                     alt="{{ $recipientName }} Avatar" 
                                     class="avatar-img"
                                     id="recipient-avatar"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="avatar-fallback" style="display:none;">
                                    {{ strtoupper(substr($recipientName, 0, 1)) }}
                                </div>
                            </div>
                            <div class="recipient-details">
                                <div class="recipient-name" id="recipient-name">{{ $recipientName }}</div>
                                <div class="recipient-username" id="recipient-username">{{ $recipientUsername }}</div>
                            </div>
                        </div>
                        
                        <div class="mpesa-details">
                            <div class="mpesa-badge">M-Pesa Number</div>
                            <div class="mpesa-number-container">
                                <span class="mpesa-number" id="mpesa-number">{{ $mpesaNumber }}</span>
                                <button type="button" class="copy-btn" onclick="copyMpesaNumber('{{ $mpesaNumber }}', this)" data-number="{{ $mpesaNumber }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Instructions -->
                <div class="instructions-section">
                    <div class="instructions-card">
                        <div class="instructions-header">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="16" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            <span>How to pay:</span>
                        </div>
                        
                        <div class="instructions-list">
                            <div class="instruction-step">
                                <span class="step-number">1</span>
                                <span class="step-text">
                                    Send <strong><span id="step-amount">{{ $totalAmount }}</span></strong> to 
                                    <strong>M-Pesa No:<span id="step-mpesa">{{ $mpesaNumber }}</span></strong> via M-Pesa
                                </span>
                            </div>
                            <div class="instruction-step">
                                <span class="step-number">2</span>
                                <span class="step-text">Copy paste the Transaction Id or the Transaction Message</span>
                            </div>
                            <div class="instruction-step">
                                <span class="step-number">3</span>
                                <span class="step-text">Return here and enter the transaction ID to complete your purchase</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction ID Input Section -->
                <div class="form-section">
                    <div class="form-group">
                        <label for="mpesa-transaction-id" class="form-label required">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14,2 14,8 20,8"/>
                            </svg>
                            M-Pesa Transaction ID
                        </label>
                        <div class="input-container">
                            <input 
                                type="text" 
                                class="form-control" 
                                id="mpesa-transaction-id" 
                                name="transaction_id"
                                placeholder="e.g., RGH1234567" 
                                maxlength="20"
                                required
                                autocomplete="off"
                            >
                            <div class="input-feedback"></div>
                        </div>
                        <div class="form-help">
                            Enter the transaction ID from your M-Pesa confirmation SMS
                        </div>
                        <div class="form-error" style="display: none;"></div>
                    </div>
                </div>

                <!-- Additional Notes Section -->
                <div class="notes-section">
                    <div class="form-group">
                        <label for="payment-notes" class="form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            Additional Notes (Optional)
                        </label>
                        <div class="input-container">
                            <textarea 
                                class="form-control" 
                                id="payment-notes" 
                                name="notes"
                                placeholder="Any additional information about your payment..." 
                                rows="3"
                                maxlength="500"
                            ></textarea>
                            <div class="character-count">
                                <span class="current">0</span>/<span class="max">500</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="new-confirmation-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Cancel
                </button>
                <button type="button" class="btn btn-confirm" id="confirmPaymentBtn" onclick="confirmNewPayment()">
                    <span class="btn-content">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20,6 9,17 4,12"/>
                        </svg>
                        Confirm Payment
                    </span>
                    <span class="btn-loading">
                        <div class="loading-spinner"></div>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Notification -->
<div class="payment-success-notification" id="paymentSuccessNotification" style="display: none;">
    <div class="success-content">
        <div class="success-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22,4 12,14.01 9,11.01"/>
            </svg>
        </div>
        <h4 class="success-title">Payment Confirmed Successfully!</h4>
        <p class="success-message">Your payment is being processed. You will receive a confirmation shortly.</p>
    </div>
</div>

<!-- Loading Overlay -->
<div class="payment-overlay" id="paymentOverlay" style="display: none;">
    <div class="overlay-content">
        <div class="loading-spinner-large"></div>
        <p class="loading-text">Processing your payment...</p>
    </div>
</div>

<!-- Success Toast -->
<div class="payment-toast" id="confirmationToast">
    <div class="toast-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22,4 12,14.01 9,11.01"/>
        </svg>
    </div>
    <div class="toast-content">
        <div class="toast-title">M-Pesa Number Copied!</div>
        <div class="toast-message">The number has been copied to your clipboard</div>
    </div>
</div>