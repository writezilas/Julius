{{-- Modern Payment Confirmation Modal --}}
{{-- This component creates a modern, responsive payment confirmation modal exactly matching the screenshot --}}

@props(['payment', 'pairedShare', 'buyerProfile'])

@php
    $buyerInfo = json_decode($pairedShare->pairedUserShare->user->business_profile);
    $showTillInfo = !empty($buyerInfo->mpesa_till_number) && !empty($buyerInfo->mpesa_till_name);
@endphp

<div class="modal fade modern-payment-confirmation-modal" id="modernPaymentModal{{ $pairedShare->id }}" 
     data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modern-payment-content">
            
            <!-- Modern Header -->
            <div class="modal-header modern-payment-header">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="ri-shield-check-line"></i>
                    </div>
                    <h5 class="modal-title">
                        Payment from {{ $pairedShare->pairedUserShare->user->name }}
                    </h5>
                </div>
                <button type="button" class="btn-close modern-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <div class="modal-body modern-payment-body">
                
                <!-- Payment Status Alert -->
                @if($payment->status === 'conformed')
                    <div class="status-alert success-alert">
                        <div class="alert-icon">
                            <i class="ri-check-double-line"></i>
                        </div>
                        <div class="alert-content">
                            <h6>Payment Completed Successfully!</h6>
                            <p>You have confirmed the buyer's payment. The transaction is now complete.</p>
                        </div>
                    </div>
                @else
                    <div class="status-alert warning-alert">
                        <div class="alert-icon">
                            <i class="ri-time-line"></i>
                        </div>
                        <div class="alert-content">
                            <h6>Payment Awaiting Confirmation</h6>
                            <p>The buyer has submitted payment details. Please review and confirm if the payment is correct.</p>
                        </div>
                    </div>
                @endif

                <!-- Buyer Information Section -->
                <div class="info-section buyer-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="ri-user-line"></i>
                        </div>
                        <h6 class="section-title">Buyer Information</h6>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="ri-user-3-line"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Buyer Name</span>
                                <span class="info-value">{{ $pairedShare->pairedUserShare->user->name }}</span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="ri-at-line"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Username</span>
                                <span class="info-value">{{ $pairedShare->pairedUserShare->user->username }}</span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="ri-smartphone-line"></i>
                            </div>
                            <div class="info-content">
                                @if ($showTillInfo)
                                    <span class="info-label">Buyer's MPESA Name</span>
                                    <span class="info-value">{{ $buyerInfo->mpesa_till_name }}</span>
                                @else
                                    <span class="info-label">Buyer's MPESA Name</span>
                                    <span class="info-value">{{ $buyerInfo->mpesa_name ?? 'N/A' }}</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="ri-phone-line"></i>
                            </div>
                            <div class="info-content">
                                @if ($showTillInfo)
                                    <span class="info-label">Buyer's MPESA Number</span>
                                    <span class="info-value">{{ $buyerInfo->mpesa_till_number }}</span>
                                @else
                                    <span class="info-label">Buyer's MPESA Number</span>
                                    <span class="info-value">{{ $buyerInfo->mpesa_no ?? 'N/A' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Submitted Details Section -->
                <div class="info-section payment-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="ri-bill-line"></i>
                        </div>
                        <h6 class="section-title">Payment Submitted Details</h6>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="ri-user-line"></i>
                            </div>
                            <div class="info-content">
                                @if ($showTillInfo)
                                    <span class="info-label">Buyer's MPESA Name</span>
                                    <span class="info-value">{{ $buyerInfo->mpesa_till_name }}</span>
                                @else
                                    <span class="info-label">Buyer's MPESA Name</span>
                                    <span class="info-value">{{ $buyerInfo->mpesa_name ?? 'N/A' }}</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="ri-smartphone-line"></i>
                            </div>
                            <div class="info-content">
                                @if ($showTillInfo)
                                    <span class="info-label">Buyer's MPESA Number</span>
                                    <span class="info-value">{{ $buyerInfo->mpesa_till_number }}</span>
                                @else
                                    <span class="info-label">Buyer's MPESA Number</span>
                                    <span class="info-value">{{ $buyerInfo->mpesa_no ?? 'N/A' }}</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="ri-money-dollar-circle-line"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Amount</span>
                                <span class="info-value amount-value">{{ formatPrice($payment->amount) }}</span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="ri-hashtag"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Transaction ID</span>
                                <span class="info-value transaction-id">{{ $payment->txs_id }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($payment->status !== 'conformed')
                    <!-- Comment Section -->
                    <div class="comment-section">
                        <form id="modernPaymentApproveForm{{$payment->id}}" action="{{ route('user.share.paymentApprove') }}" method="post">
                            @csrf
                            <input type="hidden" value="{{ $payment->id }}" name="paymentId">
                            
                            <div class="comment-group">
                                <div class="comment-header">
                                    <div class="comment-icon">
                                        <i class="ri-chat-3-line"></i>
                                    </div>
                                    <label for="modern_note_{{ $payment->id }}" class="comment-label">
                                        Your Comment <span class="optional">(Optional)</span>
                                    </label>
                                </div>
                                
                                <div class="comment-input-wrapper">
                                    <textarea 
                                        name="note_by_receiver" 
                                        id="modern_note_{{ $payment->id }}" 
                                        class="comment-textarea" 
                                        rows="4"
                                        placeholder="Add any comments about this payment..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Modern Footer -->
            <div class="modal-footer modern-payment-footer">
                @if($payment->status === 'conformed')
                    <button type="button" class="btn-modern btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i>
                        <span>Close</span>
                    </button>
                @else
                    <button type="button" class="btn-modern btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i>
                        <span>Cancel</span>
                    </button>
                    <button type="button" onclick="handleModernPaymentConfirm({{ $payment->id }})" 
                            class="btn-modern btn-primary modern-submit-{{$payment->id}}">
                        <i class="ri-check-double-line"></i>
                        <span>Confirm Payment</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>