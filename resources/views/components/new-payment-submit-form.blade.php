{{-- New Payment Submit Form - Clean, Responsive, Light/Dark Mode Compatible --}}
{{-- Usage: @include('components.new-payment-submit-form', ['user' => $user, 'share' => $share, 'totalShare' => $totalShare, 'tradePrice' => $tradePrice, 'pairedIds' => $pairedIds]) --}}

@props(['user', 'share', 'totalShare', 'tradePrice', 'pairedIds' => []])

@php
    $modalId = "newPaymentSubmitModal" . $user->id . "-" . $share->id;
    $formId = "newPaymentSubmitForm" . $user->id;
    $totalAmount = $totalShare * $tradePrice;
    
    // Get payment details using PaymentHelper
    $fullBusinessProfile = json_decode($user->business_profile);
    $paymentDetails = \App\Helpers\PaymentHelper::getPaymentDetails($fullBusinessProfile, $user);
    
    // Check if using M-Pesa Till
    $tillNumber = isset($fullBusinessProfile->mpesa_till_no) ? trim($fullBusinessProfile->mpesa_till_no) : '';
    $tillName = isset($fullBusinessProfile->mpesa_till_name) ? trim($fullBusinessProfile->mpesa_till_name) : '';
    $isUsingTill = !empty($tillNumber) && !empty($tillName);
@endphp

<!-- New Payment Submit Modal -->
<div class="modal fade" id="{{ $modalId }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newPaymentSubmitLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modern-payment-modal">
        <div class="modal-content new-payment-submit-modal modern-payment-content">
            
            <!-- Header -->
            <div class="new-payment-header">
                <div class="header-content">
                    <div class="header-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4"/>
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                    </div>
                    <div class="header-text">
                        <h4 class="modal-title" id="newPaymentSubmitLabel{{ $user->id }}">
                            Submit Payment Form
                        </h4>
                        <p class="modal-subtitle">
                            Complete your purchase of {{ number_format($totalShare) }} shares
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

            <form id="{{ $formId }}" action="{{ route('user.shares.payment') }}" method="POST">
                @csrf
                
                <div class="new-payment-body">
                    
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
                                <div class="summary-value shares-value">{{ number_format($totalShare) }}</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-label">Price per Share</div>
                                <div class="summary-value price-value">{{ formatPrice($tradePrice) }}</div>
                            </div>
                            <div class="summary-item total-item">
                                <div class="summary-label">Total Amount</div>
                                <div class="summary-value total-value">{{ formatPrice($totalAmount) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Recipient -->
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
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="recipient-details">
                                    <div class="recipient-name">{{ $user->name }}</div>
                                    <div class="recipient-username">{{ $user->username }}</div>
                                </div>
                            </div>
                            
                            <div class="mpesa-details">
                                <div class="mpesa-badge">
                                    {{ $isUsingTill ? 'M-Pesa Till' : 'M-Pesa Number' }}
                                </div>
                                <div class="mpesa-number-container">
                                    <span class="mpesa-number" id="mpesaNumber{{ $user->id }}">{{ $paymentDetails['payment_number'] }}</span>
                                    <button type="button" class="copy-btn" onclick="copyMpesaNumber('{{ $paymentDetails['payment_number'] }}', this)">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="mpesa-name">{{ $paymentDetails['payment_name'] }}</div>
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
                                        Send <strong>{{ formatPrice($totalAmount) }}</strong> to 
                                        <strong>{{ $isUsingTill ? 'M-Pesa Till' : 'M-Pesa No' }}:{{ $paymentDetails['payment_number'] }}</strong>
                                    </span>
                                </div>
                                <div class="instruction-step">
                                    <span class="step-number">2</span>
                                    <span class="step-text">Copy the transaction ID from your M-Pesa SMS</span>
                                </div>
                                <div class="instruction-step">
                                    <span class="step-number">3</span>
                                    <span class="step-text">Enter the transaction ID below and submit</span>
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
                    <div class="form-section">
                        <!-- Transaction ID Field -->
                        <div class="form-group">
                            <label for="txsId{{ $user->id }}" class="form-label required">
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
                                    id="txsId{{ $user->id }}" 
                                    name="txs_id" 
                                    value="{{ old('txs_id') }}" 
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
                            @error('txs_id')
                            <div class="form-error">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="15" y1="9" x2="9" y2="15"/>
                                    <line x1="9" y1="9" x2="15" y2="15"/>
                                </svg>
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <!-- Additional Notes Field -->
                        <div class="form-group">
                            <label for="notesSender{{ $user->id }}" class="form-label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                </svg>
                                Additional Notes (Optional)
                            </label>
                            <div class="input-container">
                                <textarea 
                                    class="form-control" 
                                    id="notesSender{{ $user->id }}" 
                                    name="note_by_sender" 
                                    placeholder="Any additional information about your payment..."
                                    rows="3"
                                    maxlength="500"
                                >{{ old('note_by_sender') }}</textarea>
                                <div class="character-count">
                                    <span class="current">0</span>/<span class="max">500</span>
                                </div>
                            </div>
                            @error('note_by_sender')
                            <div class="form-error">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="15" y1="9" x2="9" y2="15"/>
                                    <line x1="9" y1="9" x2="15" y2="15"/>
                                </svg>
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="new-payment-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-submit" id="submitBtn{{ $user->id }}" onclick="submitNewPaymentForm('{{ $formId }}', this)">
                        <span class="btn-content">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Submit Payment
                        </span>
                        <span class="btn-loading">
                            <div class="loading-spinner"></div>
                            Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Toast (Hidden by default) -->
<div class="payment-toast" id="paymentToast{{ $user->id }}">
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