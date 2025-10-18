{{-- Modern Payment Confirmation Form - Enhanced Responsive Design --}}
{{-- Usage: @include('components.modern-payment-confirmation-form', ['user' => $user, 'share' => $share, 'businessProfile' => $businessProfile, 'totalShare' => $totalShare, 'tradePrice' => $tradePrice, 'pairedIds' => $pairedIds]) --}}

@props(['user', 'share', 'businessProfile', 'totalShare', 'tradePrice', 'pairedIds' => []])

@php
    $modalId = "modernPaymentModal" . $user->id . "-" . $share->id;
    $formId = "modernPaymentForm" . $user->id;
    $totalAmount = $totalShare * $tradePrice;
    
    // Get payment details
    $fullBusinessProfile = json_decode($user->business_profile);
    $paymentDetails = \App\Helpers\PaymentHelper::getPaymentDetails($fullBusinessProfile, $user);
    
    // Check if using M-Pesa Till
    $tillNumber = isset($fullBusinessProfile->mpesa_till_no) ? trim($fullBusinessProfile->mpesa_till_no) : '';
    $tillName = isset($fullBusinessProfile->mpesa_till_name) ? trim($fullBusinessProfile->mpesa_till_name) : '';
    $isUsingTill = !empty($tillNumber) && !empty($tillName);
@endphp

<!-- Modern Payment Confirmation Modal -->
<div class="modal fade modern-payment-modal" id="{{ $modalId }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modernPaymentLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modern-modal-dialog">
        <div class="modal-content modern-modal-content">
            
            <!-- Modern Header -->
            <header class="modal-header modern-header">
                <div class="header-container">
                    <div class="header-badge">
                        <div class="badge-icon">
                            <i class="ri-secure-payment-fill" aria-hidden="true"></i>
                        </div>
                        <div class="badge-pulse" aria-hidden="true"></div>
                    </div>
                    
                    <div class="header-content">
                        <h1 class="modal-title" id="modernPaymentLabel{{ $user->id }}">
                            Submit Payment Confirmation
                        </h1>
                        <p class="modal-subtitle">
                            Complete your purchase of <strong>{{ number_format($totalShare) }} shares</strong>
                        </p>
                    </div>
                    
                    <button type="button" class="btn-close modern-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ri-close-line" aria-hidden="true"></i>
                    </button>
                </div>
            </header>

            <form id="{{ $formId }}" action="{{ route('user.shares.payment') }}" method="POST" class="modern-payment-form" novalidate>
                @csrf
                
                <!-- Main Content -->
                <main class="modal-body modern-body">
                    
                    <!-- Payment Overview Card -->
                    <section class="payment-overview" aria-labelledby="payment-overview-title">
                        <h2 id="payment-overview-title" class="section-title">
                            <i class="ri-receipt-line section-icon" aria-hidden="true"></i>
                            Payment Summary
                        </h2>
                        
                        <div class="overview-grid">
                            <!-- Shares Info -->
                            <div class="overview-item shares-item">
                                <div class="item-icon">
                                    <i class="ri-coins-line" aria-hidden="true"></i>
                                </div>
                                <div class="item-content">
                                    <span class="item-label">Shares</span>
                                    <span class="item-value">{{ number_format($totalShare) }}</span>
                                </div>
                            </div>
                            
                            <!-- Price per Share -->
                            <div class="overview-item price-item">
                                <div class="item-icon">
                                    <i class="ri-price-tag-3-line" aria-hidden="true"></i>
                                </div>
                                <div class="item-content">
                                    <span class="item-label">Price per Share</span>
                                    <span class="item-value">{{ formatPrice($tradePrice) }}</span>
                                </div>
                            </div>
                            
                            <!-- Total Amount -->
                            <div class="overview-item total-item">
                                <div class="item-icon total-icon">
                                    <i class="ri-money-dollar-circle-fill" aria-hidden="true"></i>
                                </div>
                                <div class="item-content">
                                    <span class="item-label">Total Amount</span>
                                    <span class="item-value total-value" aria-live="polite">{{ formatPrice($totalAmount) }}</span>
                                </div>
                                <div class="total-highlight" aria-hidden="true"></div>
                            </div>
                        </div>
                    </section>

                    <!-- Payment Recipient -->
                    <section class="payment-recipient" aria-labelledby="recipient-title">
                        <h2 id="recipient-title" class="section-title">
                            <i class="ri-user-settings-line section-icon" aria-hidden="true"></i>
                            Send Payment To
                        </h2>
                        
                        <div class="recipient-card">
                            <div class="recipient-profile">
                                <div class="profile-avatar">
                                    <span class="avatar-text" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    <div class="avatar-ring" aria-hidden="true"></div>
                                </div>
                                
                                <div class="profile-info">
                                    <h3 class="profile-name">{{ $user->name }}</h3>
                                    <p class="profile-username">{{ $user->username }}</p>
                                </div>
                            </div>
                            
                            <div class="payment-info">
                                <div class="payment-method">
                                    <div class="method-badge">
                                        <i class="ri-smartphone-line" aria-hidden="true"></i>
                                        {{ $isUsingTill ? 'M-Pesa Till' : 'M-Pesa' }}
                                    </div>
                                </div>
                                
                                <div class="payment-details">
                                    <div class="payment-number-container">
                                        <span class="payment-number" aria-label="{{ $isUsingTill ? 'M-Pesa Till Number' : 'M-Pesa Phone Number' }}">
                                            {{ $paymentDetails['payment_number'] }}
                                        </span>
                                        <button type="button" 
                                                class="copy-number-btn" 
                                                onclick="modernCopyToClipboard('{{ $paymentDetails['payment_number'] }}', this)"
                                                aria-label="Copy {{ $isUsingTill ? 'M-Pesa Till number' : 'M-Pesa phone number' }}"
                                                data-bs-toggle="tooltip"
                                                title="Copy number">
                                            <i class="ri-file-copy-line" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <p class="payment-name">{{ $paymentDetails['payment_name'] }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Instructions -->
                    <section class="payment-instructions" aria-labelledby="instructions-title">
                        <h2 id="instructions-title" class="section-title">
                            <i class="ri-guide-line section-icon" aria-hidden="true"></i>
                            How to pay
                        </h2>
                        
                        <div class="instructions-list">
                            <div class="instruction-step" role="listitem">
                                <div class="step-number" aria-hidden="true">1</div>
                                <div class="step-content">
                                    Send <strong class="amount-highlight">{{ formatPrice($totalAmount) }}</strong> 
                                    to <strong class="mpesa-highlight">{{ $isUsingTill ? 'M-Pesa Till No:' : 'M-Pesa No:' }}{{ $paymentDetails['payment_number'] }}</strong> 
                                    via M-Pesa
                                </div>
                            </div>
                            
                            <div class="instruction-step" role="listitem">
                                <div class="step-number" aria-hidden="true">2</div>
                                <div class="step-content">
                            Copy paste the Transaction Id or the Transaction Message
                                </div>
                            </div>
                            
                            <div class="instruction-step" role="listitem">
                                <div class="step-number" aria-hidden="true">3</div>
                                <div class="step-content">
                                    Click "Submit Payment" to complete your purchase
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Form Fields -->
                    <section class="form-fields" aria-labelledby="form-fields-title">
                        <h2 id="form-fields-title" class="sr-only">Payment Information Form</h2>
                        
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

                        <!-- Transaction ID Field -->
                        <div class="form-group transaction-group">
                            <label for="modernTxsId{{ $user->id }}" class="form-label required-label">
                                <span class="label-icon">
                                    <i class="ri-receipt-line" aria-hidden="true"></i>
                                </span>
                                <span class="label-text">M-Pesa Transaction ID</span>
                            </label>
                            
                            <div class="input-wrapper">
                                <input type="text" 
                                       class="form-input transaction-input" 
                                       id="modernTxsId{{ $user->id }}" 
                                       name="txs_id" 
                                       value="{{ old('txs_id') }}" 
                                       placeholder="e.g., RGH1234567" 
                                       autocomplete="off"
                                       aria-describedby="txs-help txs-error"
                                       required>
                                <div class="input-icon" aria-hidden="true">
                                    <i class="ri-qr-code-line"></i>
                                </div>
                                <div class="input-focus-ring" aria-hidden="true"></div>
                            </div>
                            
                            <p id="txs-help" class="form-help">
                                <i class="ri-information-line" aria-hidden="true"></i>
                                Copy the transaction ID from your M-Pesa confirmation SMS
                            </p>
                            
                            @error('txs_id')
                            <div id="txs-error" class="form-error">
                                <i class="ri-error-warning-line" aria-hidden="true"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>

                        <!-- Additional Notes Field -->
                        <div class="form-group notes-group">
                            <label for="modernNotes{{ $user->id }}" class="form-label optional-label">
                                <span class="label-icon">
                                    <i class="ri-chat-3-line" aria-hidden="true"></i>
                                </span>
                                <span class="label-text">Additional Notes</span>
                                <span class="label-optional">(Optional)</span>
                            </label>
                            
                            <div class="input-wrapper">
                                <textarea class="form-input notes-input" 
                                          id="modernNotes{{ $user->id }}" 
                                          name="note_by_sender" 
                                          placeholder="Any additional information about your payment..."
                                          rows="4"
                                          maxlength="500"
                                          aria-describedby="notes-help notes-counter notes-error">{{ old('note_by_sender') }}</textarea>
                                <div class="input-focus-ring" aria-hidden="true"></div>
                            </div>
                            
                            <div class="form-meta">
                                <p id="notes-help" class="form-help">
                                    <i class="ri-information-line" aria-hidden="true"></i>
                                    Optional additional information about your payment
                                </p>
                                <div id="notes-counter" class="character-counter" aria-live="polite">
                                    <span class="current">0</span>/<span class="max">500</span>
                                </div>
                            </div>
                            
                            @error('note_by_sender')
                            <div id="notes-error" class="form-error">
                                <i class="ri-error-warning-line" aria-hidden="true"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>
                    </section>
                </main>
                
                <!-- Footer -->
                <footer class="modal-footer modern-footer">
                    <button type="button" class="btn btn-secondary modern-btn-secondary" data-bs-dismiss="modal">
                        <span class="btn-icon">
                            <i class="ri-close-line" aria-hidden="true"></i>
                        </span>
                        <span class="btn-text">Cancel</span>
                    </button>
                    
                    <button type="button" 
                            class="btn btn-primary modern-btn-primary" 
                            id="modernSubmitBtn{{ $user->id }}" 
                            onclick="submitModernPaymentForm('{{ $formId }}', this)">
                        <span class="btn-content">
                            <span class="btn-icon">
                                <i class="ri-secure-payment-line" aria-hidden="true"></i>
                            </span>
                            <span class="btn-text">Submit Payment</span>
                        </span>
                        <span class="btn-loading">
                            <span class="loading-spinner" aria-hidden="true"></span>
                            <span class="loading-text">Submitting...</span>
                        </span>
                    </button>
                </footer>
            </form>
        </div>
    </div>
</div>

<!-- Success Notification Template -->
<template id="success-notification-template">
    <div class="success-notification" role="alert" aria-live="assertive">
        <div class="notification-icon">
            <i class="ri-check-line" aria-hidden="true"></i>
        </div>
        <div class="notification-content">
            <span class="notification-message"></span>
        </div>
    </div>
</template>

<!-- Screen Reader Announcement Area -->
<div id="modern-sr-announcements" class="sr-only" aria-live="polite" aria-atomic="true"></div>