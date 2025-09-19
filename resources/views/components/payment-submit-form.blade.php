{{-- Enhanced Payment Submit Form Modal Component --}}
{{-- Usage: @include('components.payment-submit-form', ['user' => $user, 'share' => $share, 'businessProfile' => $businessProfile, 'totalShare' => $totalShare, 'tradePrice' => $tradePrice, 'pairedIds' => $pairedIds]) --}}

@props(['user', 'share', 'businessProfile', 'totalShare', 'tradePrice', 'pairedIds' => []])

@php
    $modalId = "paymentModal" . $user->id . "-" . $share->id;
    $formId = "paymentForm" . $user->id;
    $totalAmount = $totalShare * $tradePrice;
    
    // Get appropriate payment details using PaymentHelper
    $userBusinessProfile = json_decode($user->business_profile);
    $paymentDetails = \App\Helpers\PaymentHelper::getPaymentDetails($userBusinessProfile, $user);
@endphp

<!--Enhanced Payment Modal -->
<div class="modal fade payment-modal" id="{{ $modalId }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="paymentModalLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content shadow-lg border-0">
            <!-- Enhanced Modal Header -->
            <div class="modal-header bg-gradient-primary text-white border-0">
                <div class="d-flex align-items-center">
                    <div class="payment-icon me-3">
                        <i class="ri-secure-payment-line fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-semibold mb-0 text-white" id="paymentModalLabel{{ $user->id }}">Secure Payment Submission</h5>
                        <small class="opacity-75 text-white">Complete your share purchase payment</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="{{ $formId }}" action="{{ route('user.shares.payment') }}" method="POST" class="payment-form needs-validation" novalidate>
                @csrf
                
                <div class="modal-body p-4">
                    <!-- Payment Summary Card -->
                    <div class="payment-summary-card mb-4">
                        <div class="card border-0 bg-light-subtle">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center mb-3 mb-md-0">
                                        <div class="seller-avatar mx-auto mb-2">
                                            <div class="avatar-lg bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
                                                <span class="fs-4 fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                            </div>
                                        </div>
                                        <h6 class="fw-semibold mb-1">{{ $user->name }}</h6>
                                        <small class="text-muted">{{ $user->username }}</small>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="payment-breakdown">
                                            <h6 class="text-primary mb-3">
                                                <i class="ri-information-line me-2"></i>Transaction Summary
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-sm-4">
                                                    <div class="summary-item">
                                                        <label class="small text-muted d-block">Shares to Purchase</label>
                                                        <div class="fw-bold fs-5 text-success">
                                                            <i class="ri-coins-line me-1"></i>{{ number_format($totalShare) }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="summary-item">
                                                        <label class="small text-muted d-block">Price per Share</label>
                                                        <div class="fw-bold fs-5 text-info">{{ formatPrice($tradePrice) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="summary-item">
                                                        <label class="small text-muted d-block">Total Amount</label>
                                                        <div class="fw-bold fs-4 text-primary border-start border-primary border-3 ps-2">
                                                            {{ formatPrice($totalAmount) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <div class="d-flex align-items-start">
                            <i class="ri-information-line fs-4 me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 class="alert-heading fw-semibold mb-2">
                                    <i class="ri-mobile-phone-line me-2"></i>Payment Instructions
                                </h6>
                                <p class="mb-2">Please send <strong>{{ formatPrice($totalAmount) }}</strong> to M-Pesa number:</p>
                                <div class="mpesa-number bg-white p-3 rounded border">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <code class="fs-5 fw-bold text-success">{{ $paymentDetails['payment_number'] }}</code>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="copyToClipboard('{{ $paymentDetails['payment_number'] }}')">
                                            <i class="ri-file-copy-line me-1"></i>Copy
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">Then fill in the transaction details below after completing the payment.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="row g-4">
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
                        
                        <!-- Seller M-Pesa Name Field -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control form-control-lg bg-light" id="nameField{{ $user->id }}" name="name" value="{{ $paymentDetails['payment_name'] }}" readonly>
                                <label for="nameField{{ $user->id }}">
                                    <i class="ri-user-line me-2"></i>Seller M-Pesa Name
                                </label>
                                @error('name')
                                <div class="invalid-feedback d-block">
                                    <i class="ri-error-warning-line me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Seller M-Pesa Number Field -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control form-control-lg bg-light" id="phoneField{{ $user->id }}" name="number" value="{{ $paymentDetails['payment_number'] }}" readonly>
                                <label for="phoneField{{ $user->id }}">
                                    <i class="ri-phone-line me-2"></i>Seller M-Pesa Number
                                </label>
                                <div class="form-text">
                                    <i class="ri-information-line me-1"></i>The seller's M-Pesa account details
                                </div>
                                @error('number')
                                <div class="invalid-feedback d-block">
                                    <i class="ri-error-warning-line me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Transaction ID Field -->
                        <div class="col-md-8">
                            <div class="form-floating">
                                <input type="text" class="form-control form-control-lg transaction-input" id="txsIdField{{ $user->id }}" name="txs_id" value="{{ old('txs_id') }}" placeholder="e.g., RGH1234567" required>
                                <label for="txsIdField{{ $user->id }}">
                                    <i class="ri-receipt-line me-2"></i>M-Pesa Transaction ID <span class="text-danger">*</span>
                                </label>
                                <div class="form-text">
                                    <i class="ri-information-line me-1"></i>Please enter the M-Pesa transaction ID from your payment confirmation
                                </div>
                                @error('txs_id')
                                <div class="invalid-feedback">
                                    <i class="ri-error-warning-line me-1"></i>{{ $message }}
                                </div>
                                @enderror
                                <div class="valid-feedback">
                                    <i class="ri-check-line me-1"></i>Transaction ID looks valid
                                </div>
                            </div>
                        </div>

                        <!-- Amount Field -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="number" class="form-control form-control-lg bg-light text-success fw-bold" id="amountField{{ $user->id }}" name="amount" value="{{ $totalAmount }}" readonly>
                                <label for="amountField{{ $user->id }}">
                                    <i class="ri-money-dollar-circle-line me-2"></i>Amount
                                </label>
                                @error('amount')
                                <div class="invalid-feedback d-block">
                                    <i class="ri-error-warning-line me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Note Field -->
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" id="noteField{{ $user->id }}" name="note_by_sender" style="height: 100px" placeholder="Any additional notes...">{{ old('note_by_sender') }}</textarea>
                                <label for="noteField{{ $user->id }}">
                                    <i class="ri-message-3-line me-2"></i>Additional Notes (Optional)
                                </label>
                                @error('note_by_sender')
                                <div class="invalid-feedback">
                                    <i class="ri-error-warning-line me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="alert alert-warning border-0 shadow-sm mt-4">
                        <div class="d-flex align-items-center">
                            <i class="ri-shield-check-line fs-4 me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="fw-semibold mb-1">Security Notice</h6>
                                <small class="mb-0">Never share your transaction details with unauthorized parties. This information will be verified by the seller.</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Enhanced Footer -->
                <div class="modal-footer bg-light border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <button type="button" class="btn btn-secondary btn-lg px-4" data-bs-dismiss="modal">
                            <i class="ri-close-line me-2"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary btn-lg px-5 submit-payment-btn" id="submitBtn{{ $user->id }}" 
                                onclick="submitPaymentForm('{{ $formId }}', this)">
                            <i class="ri-secure-payment-line me-2"></i>Submit Payment Details
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Copy to clipboard functionality
    function copyToClipboard(text) {
        if (text && text !== 'Not Set') {
            navigator.clipboard.writeText(text).then(function() {
                // Show success message
                const btn = event.target.closest('button');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="ri-check-line me-1"></i>Copied!';
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-success');
                
                setTimeout(function() {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-success');
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                alert('Failed to copy to clipboard');
            });
        }
    }
</script>
@endpush
