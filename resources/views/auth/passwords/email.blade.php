@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.password-reset')
@endsection

@section('css')
<style>
    /* Enhanced Password Reset Page Styles - Consistent with Login */
    .auth-page-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }
    
    .auth-one-bg {
        position: relative;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .auth-one-bg::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/><circle cx="25" cy="25" r="0.5" fill="%23ffffff" opacity="0.05"/><circle cx="75" cy="75" r="0.8" fill="%23ffffff" opacity="0.08"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>')
        opacity: 0.3;
        animation: float 20s infinite linear;
    }
    
    @keyframes float {
        0% { transform: translateX(-100px); }
        100% { transform: translateX(100px); }
    }
    
    .bg-overlay {
        background: rgba(0, 0, 0, 0.2) !important;
    }
    
    .auth-logo {
        transition: transform 0.3s ease;
    }
    
    .auth-logo:hover {
        transform: scale(1.05);
    }
    
    .auth-card {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1), 0 15px 35px rgba(0, 0, 0, 0.05);
        transition: all 0.4s ease;
        overflow: hidden;
        position: relative;
    }
    
    .auth-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }
    
    .auth-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 35px 55px rgba(0, 0, 0, 0.15), 0 25px 45px rgba(0, 0, 0, 0.08);
    }
    
    .auth-card .card-body {
        padding: 2.5rem 2rem;
    }
    
    .welcome-title {
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
        text-align: center;
    }
    
    .welcome-subtitle {
        color: #6c757d;
        text-align: center;
        margin-bottom: 2rem;
        font-size: 0.95rem;
    }
    
    .reset-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
        color: #667eea;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
        }
    }
    
    .form-floating {
        position: relative;
        margin-bottom: 1.5rem;
    }
    
    .form-floating .form-control {
        height: 58px;
        padding: 1rem 1rem 0.25rem;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        background-color: #fff;
        transition: all 0.3s ease;
        font-size: 1rem;
    }
    
    .form-floating .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }
    
    .form-floating .form-control:not(:placeholder-shown),
    .form-floating .form-control:focus {
        padding-top: 1.625rem;
        padding-bottom: 0.625rem;
    }
    
    .form-floating label {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        padding: 1rem;
        pointer-events: none;
        border: 2px solid transparent;
        transform-origin: 0 0;
        transition: opacity 0.3s ease, transform 0.3s ease;
        font-weight: 500;
        color: #6c757d;
    }
    
    .form-floating .form-control:focus ~ label,
    .form-floating .form-control:not(:placeholder-shown) ~ label {
        opacity: 0.75;
        transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        color: #667eea;
    }
    
    .reset-btn {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        border-radius: 12px;
        padding: 0.875rem 2rem;
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: 0.025em;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        width: 100%;
        height: 56px;
    }
    
    .reset-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .reset-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }
    
    .reset-btn:hover::before {
        left: 100%;
    }
    
    .reset-btn:active {
        transform: translateY(0);
    }
    
    .reset-btn:focus {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
    }
    
    .reset-btn .btn-loading {
        display: none;
    }
    
    .reset-btn.loading .btn-text {
        display: none;
    }
    
    .reset-btn.loading .btn-loading {
        display: inline-block;
    }
    
    .spinner {
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .signin-link {
        text-align: center;
        margin-top: 2rem;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.95rem;
    }
    
    .signin-link a {
        color: #fff;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border-bottom: 2px solid transparent;
    }
    
    .signin-link a:hover {
        border-bottom-color: #fff;
        color: #fff;
    }
    
    .alert {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
        animation: slideIn 0.5s ease-out;
    }
    
    .alert-success {
        background-color: rgba(16, 185, 129, 0.1);
        color: #065f46;
        border-left: 4px solid #10b981;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .footer {
        background: transparent;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.8);
    }
    
    .footer .text-muted {
        color: rgba(255, 255, 255, 0.7) !important;
    }
    
    .footer a {
        color: rgba(255, 255, 255, 0.9);
    }
    
    .footer a:hover {
        color: #fff;
    }
    
    /* Invalid feedback styling */
    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        font-weight: 500;
    }
    
    .form-control.is-invalid {
        border-color: #dc3545;
        background-image: none;
    }
    
    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
    }
    
    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .auth-card {
            margin: 1rem;
            border-radius: 16px;
        }
        
        .auth-card .card-body {
            padding: 2rem 1.5rem;
        }
        
        .welcome-title {
            font-size: 1.5rem;
        }
        
        .form-floating .form-control {
            height: 54px;
        }
        
        .reset-btn {
            height: 52px;
            font-size: 0.95rem;
        }
        
        .reset-icon {
            width: 70px;
            height: 70px;
            font-size: 1.8rem;
        }
    }
    
    @media (max-width: 576px) {
        .auth-card {
            margin: 0.5rem;
            border-radius: 12px;
        }
        
        .auth-card .card-body {
            padding: 1.5rem 1.25rem;
        }
        
        .welcome-title {
            font-size: 1.35rem;
        }
    }
    
    /* Animation for form elements */
    .form-floating {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.6s ease forwards;
    }
    
    .form-floating { animation-delay: 0.2s; }
    .reset-btn { animation-delay: 0.3s; }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection

@section('content')
<div class="auth-page-wrapper">
    <!-- auth page bg -->
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
        <div class="bg-overlay"></div>
        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
            </svg>
        </div>
    </div>

    <!-- auth page content -->
    <div class="auth-page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mt-sm-5 mb-4 text-white">
                        <div>
                            <a href="index" class="d-inline-block auth-logo">
                                <img src="{{ URL::asset('assets/images/autobidder_light.png') }}" alt="Auto Bidder Logo" height="32">
                            </a>
                        </div>
                        <p class="mt-3 fs-16 fw-medium opacity-75">Your Preferred Trading Partner</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card auth-card mt-4">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="reset-icon">
                                    <i class="ri-lock-unlock-line"></i>
                                </div>
                                <h2 class="welcome-title">Forgot Password?</h2>
                                <p class="welcome-subtitle">No worries! Enter your email and we'll send you reset instructions</p>
                            </div>
                            
                            @if (session('status'))
                                <div class="alert alert-success text-center mb-4" role="alert">
                                    <i class="ri-check-circle-line me-2"></i>
                                    {{ session('status') }}
                                </div>
                            @endif
                            
                            <form class="form-horizontal" method="POST" action="{{ route('password.email') }}" id="resetForm">
                                @csrf
                                
                                <div class="form-floating">
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="useremail" 
                                           name="email" 
                                           placeholder="Enter your email address"
                                           value="{{ old('email') }}"
                                           required
                                           autofocus>
                                    <label for="useremail">Email Address</label>
                                    @error('email')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn reset-btn" id="resetBtn">
                                    <span class="btn-text">Send Reset Link</span>
                                    <span class="btn-loading">
                                        <span class="spinner"></span>
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="signin-link">
                        <p class="mb-0">Remember your password? <a href="{{ route('login') }}">Sign in here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center">
                        <p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> {{env('APP_NAME', 'Auto Bidder')}}. Crafted with <i class="mdi mdi-heart text-danger"></i> by <a href="#">Our Team</a></p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>
@endsection
@section('script')
<script src="{{ URL::asset('assets/libs/particles.js/particles.js.min.js') }}"></script>
<script src="{{ URL::asset('assets/js/pages/particles.app.js') }}"></script>

<script>
// Enhanced password reset functionality
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetForm');
    const resetBtn = document.getElementById('resetBtn');
    const emailInput = document.getElementById('useremail');
    
    // Form validation
    function validateForm() {
        const email = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        let isValid = true;
        
        // Email validation
        if (!email) {
            showFieldError(emailInput, 'Email address is required');
            isValid = false;
        } else if (!emailRegex.test(email)) {
            showFieldError(emailInput, 'Please enter a valid email address');
            isValid = false;
        } else {
            clearFieldError(emailInput);
        }
        
        return isValid;
    }
    
    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        feedback.innerHTML = '<strong>' + message + '</strong>';
    }
    
    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback && !feedback.querySelector('strong')) {
            feedback.remove();
        }
    }
    
    // Real-time email validation
    emailInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.value.trim())) {
                showFieldError(this, 'Please enter a valid email address');
            } else {
                clearFieldError(this);
            }
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        resetBtn.classList.add('loading');
        resetBtn.disabled = true;
        
        // Add a timeout to prevent infinite loading
        setTimeout(function() {
            if (resetBtn.classList.contains('loading')) {
                resetBtn.classList.remove('loading');
                resetBtn.disabled = false;
            }
        }, 10000);
    });
    
    // Enter key handling
    emailInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (validateForm()) {
                form.submit();
            }
        }
    });
});
</script>
@endsection
