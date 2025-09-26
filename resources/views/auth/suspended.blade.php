@extends('layouts.master-without-nav')
@section('title')
Account Suspended
@endsection
@section('css')
<style>
    .suspension-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px;
    }
    .suspension-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        text-align: center;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        max-width: 500px;
        width: 100%;
    }
    .suspension-icon {
        font-size: 80px;
        color: #f59e0b;
        margin-bottom: 20px;
    }
    .countdown-container {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin: 20px 0;
        border: 2px solid #e9ecef;
    }
    .countdown-time {
        font-size: 2.5rem;
        font-weight: bold;
        color: #dc3545;
        font-family: 'Courier New', monospace;
    }
    .countdown-label {
        color: #6c757d;
        font-size: 0.9rem;
        margin-top: 5px;
    }
    .suspension-message {
        color: #495057;
        font-size: 1.1rem;
        line-height: 1.6;
        margin: 20px 0;
    }
    .suspension-details {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        padding: 15px;
        margin: 20px 0;
        font-size: 0.95rem;
        color: #856404;
    }
    .btn-back {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        color: white;
        font-weight: 500;
        transition: transform 0.2s;
    }
    .btn-back:hover {
        transform: translateY(-2px);
        color: white;
    }
    .support-info {
        margin-top: 30px;
        padding: 15px;
        background: #e3f2fd;
        border-radius: 8px;
        border-left: 4px solid #2196f3;
    }
</style>
@endsection

@section('content')
<div class="suspension-container">
    <div class="suspension-card">
        <div class="suspension-icon">
            <i class="ri-user-forbid-line"></i>
        </div>
        
        <h1 class="h3 mb-3">Account Suspended</h1>
        
        <div class="suspension-message">
            @if($user->suspension_reason === 'payment_failure')
                <strong>Your account has been automatically suspended due to 3 consecutive payment failures.</strong><br><br>
                You have been logged out from all devices and sessions. Your account will be automatically reactivated when the suspension period expires.
            @elseif($user->suspension_reason === 'manual')
                <strong>Your account has been suspended by an administrator.</strong><br><br>
                You have been logged out from all devices and sessions. Please contact support if you believe this was done in error.
            @elseif($user->suspension_reason === 'automatic')
                <strong>Your account has been automatically suspended by the system.</strong><br><br>
                You have been logged out from all devices and sessions. The suspension will be reviewed and lifted when appropriate.
            @else
                <strong>Your account has been suspended.</strong><br><br>
                You have been logged out from all devices and sessions. Please contact support for more information.
            @endif
        </div>
        
        <div class="suspension-details">
            <strong>Suspension Details:</strong><br>
            <small>Account: {{ $user->username ?? 'N/A' }} ({{ $user->email ?? 'N/A' }})</small><br>
            @if($user->suspension_until)
                <small>Suspended until: {{ $user->suspension_until->format('F d, Y \a\t H:i') }}</small>
            @else
                <small>Suspension Duration: Indefinite</small>
            @endif
        </div>
        
        @if($user->suspension_until)
        <div class="countdown-container">
            <div class="countdown-time" id="countdown-display">
                Loading...
            </div>
            <div class="countdown-label">
                Time remaining until account reactivation
            </div>
        </div>
        @else
        <div class="countdown-container">
            <div class="countdown-time" style="color: #dc3545; font-size: 1.8rem;">
                <i class="ri-time-line me-2"></i>INDEFINITE
            </div>
            <div class="countdown-label">
                Please contact support for assistance with your account
            </div>
        </div>
        @endif
        
        <div class="support-info">
            <i class="ri-customer-service-2-line me-2"></i>
            <strong>Need Help?</strong><br>
            <small>If you believe this suspension was made in error or have questions, please contact our support team.</small>
        </div>
        
        <div class="mt-4">
            <a href="{{ route('login') }}" class="btn btn-back">
                <i class="ri-arrow-left-line me-2"></i>Back to Login
            </a>
        </div>
    </div>
</div>
@endsection

@section('script')
@if($user->suspension_until)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const suspensionUntil = new Date('{{ $user->suspension_until->toISOString() }}');
    const countdownDisplay = document.getElementById('countdown-display');
    
    if (!countdownDisplay) {
        console.error('Countdown display element not found');
        return;
    }
    
    function updateCountdown() {
        const now = new Date();
        const diff = suspensionUntil - now;
        
        if (diff <= 0) {
            countdownDisplay.innerHTML = '<span style="color: #28a745;">EXPIRED</span>';
            const labelElement = countdownDisplay.nextElementSibling;
            if (labelElement) {
                labelElement.textContent = 'Your account should now be reactivated. Please try logging in again.';
            }
            
            // Auto redirect after 5 seconds
            setTimeout(() => {
                window.location.href = '{{ route('login') }}';
            }, 5000);
            return;
        }
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        let countdownText = '';
        
        if (days > 0) {
            countdownText += `${days}<span style="font-size: 1rem;">d</span> `;
        }
        if (days > 0 || hours > 0) {
            countdownText += `${hours.toString().padStart(2, '0')}<span style="font-size: 1rem;">h</span> `;
        }
        if (days > 0 || hours > 0 || minutes > 0) {
            countdownText += `${minutes.toString().padStart(2, '0')}<span style="font-size: 1rem;">m</span> `;
        }
        countdownText += `${seconds.toString().padStart(2, '0')}<span style="font-size: 1rem;">s</span>`;
        
        countdownDisplay.innerHTML = countdownText;
    }
    
    // Update immediately and then every second
    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>
@endif
<script>
// Console logging for debugging
console.log('Suspended user page loaded');
console.log('User:', '{{ $user->username ?? "N/A" }}');
console.log('Status:', '{{ $user->status ?? "N/A" }}');
console.log('Suspension until:', '{{ $user->suspension_until ? $user->suspension_until->toISOString() : "null" }}');
</script>
@endsection
