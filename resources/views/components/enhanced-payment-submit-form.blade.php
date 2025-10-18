{{-- Enhanced Payment Submit Form - Modern and User-Friendly Design --}}
{{-- Usage: @include('components.enhanced-payment-submit-form', ['user' => $user, 'share' => $share, 'businessProfile' => $businessProfile, 'totalShare' => $totalShare, 'tradePrice' => $tradePrice, 'pairedIds' => $pairedIds]) --}}

@props(['user', 'share', 'businessProfile', 'totalShare', 'tradePrice', 'pairedIds' => []])

@php
    $modalId = "enhancedPaymentModal" . $user->id . "-" . $share->id;
    $formId = "enhancedPaymentForm" . $user->id;
    $totalAmount = $totalShare * $tradePrice;
    
    // Get payment details
    $fullBusinessProfile = json_decode($user->business_profile);
    $paymentDetails = \App\Helpers\PaymentHelper::getPaymentDetails($fullBusinessProfile, $user);
    
    // Check if using M-Pesa Till
    $tillNumber = isset($fullBusinessProfile->mpesa_till_no) ? trim($fullBusinessProfile->mpesa_till_no) : '';
    $tillName = isset($fullBusinessProfile->mpesa_till_name) ? trim($fullBusinessProfile->mpesa_till_name) : '';
    $isUsingTill = !empty($tillNumber) && !empty($tillName);
@endphp

<!-- Enhanced Payment Submit Form Modal -->
<div class="modal fade enhanced-payment-modal" id="{{ $modalId }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="enhancedPaymentLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content enhanced-modal-content">
            
            <!-- Enhanced Header with Gradient Background -->
            <div class="modal-header enhanced-header">
                <div class="header-content">
                    <div class="header-icon-container">
                        <div class="header-icon-wrapper">
                            <i class="ri-secure-payment-line header-icon"></i>
                        </div>
                        <div class="header-glow"></div>
                    </div>
                    <div class="header-text">
                        <h4 class="modal-title" id="enhancedPaymentLabel{{ $user->id }}">
                            Payment Submit Form
                        </h4>
                        <p class="modal-subtitle">Complete your purchase of {{ number_format($totalShare) }} shares</p>
                    </div>
                </div>
                <button type="button" class="btn-close enhanced-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <form id="{{ $formId }}" action="{{ route('user.shares.payment') }}" method="POST" class="enhanced-payment-form">
                @csrf
                
                <div class="modal-body enhanced-body">
                    
                    <!-- Payment Summary Card with Animation -->
                    <div class="enhanced-card payment-summary-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="ri-receipt-line me-2"></i>Payment Summary
                            </h5>
                            <div class="card-decoration"></div>
                        </div>
                        <div class="card-body">
                            <div class="summary-grid">
                                <div class="summary-item">
                                    <div class="summary-icon shares-icon">
                                        <i class="ri-coins-line"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">Shares</div>
                                        <div class="summary-value text-success">{{ number_format($totalShare) }}</div>
                                    </div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-icon price-icon">
                                        <i class="ri-price-tag-3-line"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">Price per Share</div>
                                        <div class="summary-value text-info">{{ formatPrice($tradePrice) }}</div>
                                    </div>
                                </div>
                                <div class="summary-item total-item">
                                    <div class="summary-icon total-icon">
                                        <i class="ri-money-dollar-circle-line"></i>
                                    </div>
                                    <div class="summary-content">
                                        <div class="summary-label">Total Amount</div>
                                        <div class="summary-value total-value">{{ formatPrice($totalAmount) }}</div>
                                    </div>
                                    <div class="total-glow"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Send Payment To Card -->
                    <div class="enhanced-card seller-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="ri-user-line me-2"></i>Send Payment To
                            </h5>
                            <div class="card-decoration"></div>
                        </div>
                        <div class="card-body">
                            <div class="seller-info-container">
                                <div class="seller-profile">
                                    <div class="seller-avatar-wrapper">
                                        <div class="seller-avatar">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="avatar-ring"></div>
                                    </div>
                                    <div class="seller-details">
                                        <h6 class="seller-name">{{ $user->name }}</h6>
                                        <p class="seller-username">{{ $user->username }}</p>
                                    </div>
                                </div>
                                
                                <div class="mpesa-details">
                                    <div class="mpesa-number-container">
                                        <div class="mpesa-badge">
                                            <i class="ri-smartphone-line"></i>
                                            {{ $isUsingTill ? 'M-Pesa Till' : 'M-Pesa Number' }}
                                        </div>
                                        <div class="mpesa-number-display">
                                            <span class="mpesa-number">{{ $paymentDetails['payment_number'] }}</span>
                                            <button type="button" class="copy-btn" onclick="copyToClipboard('{{ $paymentDetails['payment_number'] }}', this)" data-bs-toggle="tooltip" title="Copy {{ $isUsingTill ? 'M-Pesa Till' : 'M-Pesa Number' }}">
                                                <i class="ri-file-copy-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="mpesa-name">{{ $paymentDetails['payment_name'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- How to Pay Instructions -->
                    <div class="enhanced-card instructions-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="ri-information-line me-2"></i>How to pay:
                            </h5>
                            <div class="card-decoration"></div>
                        </div>
                        <div class="card-body">
                            <div class="payment-steps">
                                <div class="step-item" data-aos="slide-right" data-aos-delay="400">
                                    <div class="step-number">1</div>
                                    <div class="step-content">
                                        @if($isUsingTill)
                                            Send <strong class="amount-highlight">{{ formatPrice($totalAmount) }}</strong> to <strong class="mpesa-highlight">Mpesa Till:{{ $paymentDetails['payment_number'] }}</strong> via M-Pesa
                                        @else
                                            Send <strong class="amount-highlight">{{ formatPrice($totalAmount) }}</strong> to <strong class="mpesa-highlight">Mpesa No:{{ $paymentDetails['payment_number'] }}</strong> via M-Pesa
                                        @endif
                                    </div>
                                </div>
                                <div class="step-item" data-aos="slide-right" data-aos-delay="500">
                                    <div class="step-number">2</div>
                                    <div class="step-content">
                                Copy paste the Transaction Id or the Transaction Message
                                    </div>
                                </div>
                                <div class="step-item" data-aos="slide-right" data-aos-delay="600">
                                    <div class="step-number">3</div>
                                    <div class="step-content">
                                        Click "Submit Payment" to complete your purchase
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Form Fields -->
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

                    <!-- Form Fields Section -->
                    <div class="form-section" data-aos="fade-up" data-aos-delay="700">
                        
                        <!-- Transaction ID Field -->
                        <div class="enhanced-form-group">
                            <label class="enhanced-form-label required">
                                <i class="ri-receipt-line"></i>
                                M-Pesa Transaction ID
                            </label>
                            <div class="input-container">
                                <input type="text" 
                                       class="enhanced-form-control transaction-input" 
                                       id="enhancedTxsIdField{{ $user->id }}" 
                                       name="txs_id" 
                                       value="{{ old('txs_id') }}" 
                                       placeholder="e.g., RGH1234567" 
                                       required>
                                <div class="input-icon">
                                    <i class="ri-qr-code-line"></i>
                                </div>
                                <div class="input-underline"></div>
                            </div>
                            <div class="form-help">
                                <i class="ri-information-line"></i>
                                Copy the transaction ID from your M-Pesa confirmation SMS
                            </div>
                            @error('txs_id')
                            <div class="enhanced-error">
                                <i class="ri-error-warning-line"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>

                        <!-- Additional Notes Field -->
                        <div class="enhanced-form-group">
                            <label class="enhanced-form-label optional">
                                <i class="ri-chat-3-line"></i>
                                Additional Notes (Optional)
                            </label>
                            <div class="input-container">
                                <textarea class="enhanced-form-control notes-input" 
                                          id="enhancedNoteField{{ $user->id }}" 
                                          name="note_by_sender" 
                                          placeholder="Any additional information about your payment..."
                                          rows="4">{{ old('note_by_sender') }}</textarea>
                                <div class="input-underline"></div>
                            </div>
                            @error('note_by_sender')
                            <div class="enhanced-error">
                                <i class="ri-error-warning-line"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Enhanced Footer -->
                <div class="modal-footer enhanced-footer">
                    <button type="button" class="enhanced-btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i>Cancel
                    </button>
                    <button type="button" 
                            class="enhanced-btn btn-primary submit-btn" 
                            id="enhancedSubmitBtn{{ $user->id }}" 
                            onclick="submitEnhancedPaymentForm('{{ $formId }}', this)">
                        <span class="btn-content">
                            <i class="ri-secure-payment-line"></i>Submit Payment
                        </span>
                        <span class="btn-loader">
                            <span class="spinner-border spinner-border-sm"></span>Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>