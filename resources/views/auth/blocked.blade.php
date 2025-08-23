@extends('layouts.master-without-nav')
@section('title')
Account Permanently Deactivated
@endsection
@section('css')
<style>
    .blocked-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #dc3545 0%, #721c24 100%);
        padding: 20px;
    }
    .blocked-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        text-align: center;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        max-width: 500px;
        width: 100%;
    }
    .blocked-icon {
        font-size: 100px;
        color: #dc3545;
        margin-bottom: 20px;
        animation: shake 2s ease-in-out infinite;
    }
    .blocked-message {
        color: #495057;
        font-size: 1.1rem;
        line-height: 1.6;
        margin: 20px 0;
    }
    .blocked-details {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 8px;
        padding: 15px;
        margin: 20px 0;
        font-size: 0.95rem;
        color: #721c24;
    }
    .btn-back {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        color: white;
        font-weight: 500;
        transition: transform 0.2s;
        margin-right: 10px;
    }
    .btn-back:hover {
        transform: translateY(-2px);
        color: white;
    }
    .btn-contact {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        color: white;
        font-weight: 500;
        transition: transform 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-contact:hover {
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }
    .support-info {
        margin-top: 30px;
        padding: 20px;
        background: #d1ecf1;
        border-radius: 8px;
        border-left: 4px solid #17a2b8;
    }
    .deactivation-reason {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        padding: 15px;
        margin: 20px 0;
        font-size: 0.95rem;
        color: #856404;
    }
    @keyframes shake {
        0%, 20%, 50%, 80%, 100% {
            transform: translateX(0);
        }
        10% {
            transform: translateX(-5px);
        }
        30% {
            transform: translateX(5px);
        }
        40% {
            transform: translateX(-3px);
        }
        60% {
            transform: translateX(3px);
        }
        70% {
            transform: translateX(-2px);
        }
        90% {
            transform: translateX(2px);
        }
    }
</style>
@endsection

@section('content')
<div class="blocked-container">
    <div class="blocked-card">
        <div class="blocked-icon">
            <i class="ri-lock-2-line"></i>
        </div>
        
        <h1 class="h3 mb-3 text-danger">Account Permanently Deactivated</h1>
        
        <div class="blocked-message">
            Your account has been <strong>permanently deactivated</strong> and access to all services has been terminated.
        </div>
        
        <div class="blocked-details">
            <strong>Account Information:</strong><br>
            <small>Username: {{ $user->username }}</small><br>
            <small>Email: {{ $user->email }}</small><br>
            <small>Status: Permanently Blocked</small>
        </div>
        
        <div class="deactivation-reason">
            <i class="ri-information-line me-2"></i>
            <strong>Important Notice:</strong><br>
            <small>This deactivation is permanent and cannot be reversed through normal account recovery processes. All associated data and services have been terminated.</small>
        </div>
        
        <div class="support-info">
            <i class="ri-customer-service-2-line me-2"></i>
            <strong>Need Assistance?</strong><br>
            <small>
                If you believe this deactivation was made in error or if you have questions regarding this action, 
                please contact our support team immediately. Include your account information and any relevant details.
            </small>
            <br><br>
            <small>
                <strong>Support Contact:</strong><br>
                Email: support@autobidder.com<br>
                Phone: +1 (555) 123-4567
            </small>
        </div>
        
        <div class="mt-4">
            <a href="{{ route('login') }}" class="btn btn-back">
                <i class="ri-arrow-left-line me-2"></i>Back to Login
            </a>
            <a href="mailto:support@autobidder.com?subject=Account Deactivation Inquiry - {{ $user->username }}" class="btn btn-contact">
                <i class="ri-mail-line me-2"></i>Contact Support
            </a>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                <i class="ri-shield-check-line me-1"></i>
                This action was taken in accordance with our Terms of Service and Community Guidelines.
            </small>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Log the blocked access attempt for security purposes
    console.log('Blocked account access attempt detected');
    
    // Optional: Add any additional tracking or analytics here
    // if needed for security monitoring
});
</script>
@endsection
