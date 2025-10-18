@extends('layouts.master-without-nav')
@section('title')
@lang('translation.signin')
@endsection

@section('css')
<style>
    /* Enhanced Login Page Styles */
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
    
    .password-input-group {
        position: relative;
    }
    
    .password-toggle-btn {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        z-index: 5;
    }
    
    .password-toggle-btn:hover {
        background-color: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    .forgot-password-link {
        font-size: 0.9rem;
        color: #667eea;
        text-decoration: none;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .forgot-password-link:hover {
        color: #5a6fd8;
        text-decoration: underline;
    }
    
    .custom-checkbox {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
        gap: 0.75rem;
    }
    
    .custom-checkbox input[type="checkbox"] {
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        position: relative;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .custom-checkbox input[type="checkbox"]:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
    
    .custom-checkbox input[type="checkbox"]:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 14px;
        font-weight: bold;
    }
    
    .custom-checkbox label {
        color: #6c757d;
        font-size: 0.95rem;
        cursor: pointer;
        margin: 0;
        user-select: none;
    }
    
    .signin-btn {
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
    
    .signin-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .signin-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }
    
    .signin-btn:hover::before {
        left: 100%;
    }
    
    .signin-btn:active {
        transform: translateY(0);
    }
    
    .signin-btn:focus {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
    }
    
    .signin-btn .btn-loading {
        display: none;
    }
    
    .signin-btn.loading .btn-text {
        display: none;
    }
    
    .signin-btn.loading .btn-loading {
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
    
    .signup-link {
        text-align: center;
        margin-top: 2rem;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.95rem;
    }
    
    .signup-link a {
        color: #fff;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border-bottom: 2px solid transparent;
    }
    
    .signup-link a:hover {
        border-bottom-color: #fff;
        color: #fff;
    }
    
    .alert {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
    }
    
    .alert-warning {
        background-color: rgba(255, 193, 7, 0.1);
        color: #856404;
        border-left: 4px solid #ffc107;
    }
    
    .alert-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #721c24;
        border-left: 4px solid #dc3545;
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
        
        .signin-btn {
            height: 52px;
            font-size: 0.95rem;
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
    
    /* Accessibility improvements */
    .form-control:focus,
    .signin-btn:focus,
    .password-toggle-btn:focus {
        outline: 2px solid transparent;
        outline-offset: 2px;
    }
    
    /* Animation for form elements */
    .form-floating {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.6s ease forwards;
    }
    
    .form-floating:nth-child(1) { animation-delay: 0.1s; }
    .form-floating:nth-child(2) { animation-delay: 0.2s; }
    .custom-checkbox { animation-delay: 0.3s; }
    .signin-btn { animation-delay: 0.4s; }
    
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
    <!-- auth page content -->
    <div class="auth-page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mt-sm-5 mb-4 text-white">
                        <div>
                            <a href="index" class="d-inline-block auth-logo">
                                <img src="{{ URL::asset('assets/images/autobidder_logo.svg')}}" alt="Auto Bidder Logo" height="40" class="autobidder-logo auth-logo-img">
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
                            <div class="mb-4">
                                <h2 class="welcome-title">Welcome Back!</h2>
                                <p class="welcome-subtitle">Sign in to your account to continue</p>
                            </div>
                            
                            @if(session('suspension_message'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="ri-alert-line me-2"></i>
                                <strong>Account Suspended!</strong><br>
                                {{ session('suspension_message') }}
                                @if(session('suspension_until'))
                                    <br><small class="text-muted">Suspended until: {{ \Carbon\Carbon::parse(session('suspension_until'))->format('F d, Y \a\t H:i') }}</small>
                                @endif
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            @endif
                            
                            @if(session('auto_logged_out'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="ri-logout-circle-line me-2"></i>
                                <strong>Automatically Logged Out!</strong><br>
                                You have been logged out due to account suspension for consecutive payment failures.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            @endif

                            <form action="{{ route('login') }}" method="POST" id="loginForm">
                                @csrf
                                
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control @error('login') is-invalid @enderror" 
                                           id="login" 
                                           name="login" 
                                           placeholder="Enter your email or username" 
                                           value="{{ old('login') }}"
                                           required
                                           autocomplete="username"
                                           autofocus>
                                    <label for="login">Email or Username</label>
                                    @error('login')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-floating password-input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Enter your password"
                                           required
                                           autocomplete="current-password">
                                    <label for="password">Password</label>
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword()" tabindex="-1" aria-label="Toggle password visibility">
                                        <i class="ri-eye-fill" id="toggleIcon"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="custom-checkbox">
                                        <input type="checkbox" id="remember" name="remember">
                                        <label for="remember">Remember me</label>
                                    </div>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="forgot-password-link">Forgot password?</a>
                                    @endif
                                </div>

                                <button type="submit" class="btn signin-btn" id="signinBtn">
                                    <span class="btn-text">Sign In</span>
                                    <span class="btn-loading">
                                        <span class="spinner"></span>
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="signup-link">
                        <p class="mb-0">Don't have an account? <a href="register">Create one here</a></p>
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
<script>
// Enhanced login page functionality
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const signinBtn = document.getElementById('signinBtn');
    const loginInput = document.getElementById('login');
    const passwordInput = document.getElementById('password');
    
    // Helper function to check if input is email format
    function isEmail(input) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(input);
    }
    
    // Form validation
    function validateForm() {
        const login = loginInput.value.trim();
        const password = passwordInput.value;
        
        let isValid = true;
        
        // Login validation (email or username)
        if (!login) {
            showFieldError(loginInput, 'Email or username is required');
            isValid = false;
        } else if (isEmail(login)) {
            // If it looks like an email, validate email format
            if (!isEmail(login)) {
                showFieldError(loginInput, 'Please enter a valid email address');
                isValid = false;
            } else {
                clearFieldError(loginInput);
            }
        } else {
            // Username validation
            if (login.length < 3) {
                showFieldError(loginInput, 'Username must be at least 3 characters');
                isValid = false;
            } else {
                clearFieldError(loginInput);
            }
        }
        
        // Password validation
        if (!password) {
            showFieldError(passwordInput, 'Password is required');
            isValid = false;
        } else if (password.length < 6) {
            showFieldError(passwordInput, 'Password must be at least 6 characters');
            isValid = false;
        } else {
            clearFieldError(passwordInput);
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
        if (feedback) {
            feedback.remove();
        }
    }
    
    // Real-time validation
    loginInput.addEventListener('blur', function() {
        const value = this.value.trim();
        if (value) {
            if (isEmail(value)) {
                // Validate as email
                if (!isEmail(value)) {
                    showFieldError(this, 'Please enter a valid email address');
                } else {
                    clearFieldError(this);
                }
            } else {
                // Validate as username
                if (value.length < 3) {
                    showFieldError(this, 'Username must be at least 3 characters');
                } else {
                    clearFieldError(this);
                }
            }
        }
    });
    
    passwordInput.addEventListener('blur', function() {
        if (this.value && this.value.length < 6) {
            showFieldError(this, 'Password must be at least 6 characters');
        } else if (this.value) {
            clearFieldError(this);
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        signinBtn.classList.add('loading');
        signinBtn.disabled = true;
        
        // Add a timeout to prevent infinite loading in case of network issues
        setTimeout(function() {
            if (signinBtn.classList.contains('loading')) {
                signinBtn.classList.remove('loading');
                signinBtn.disabled = false;
            }
        }, 10000);
    });
    
    // Focus management
    loginInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            passwordInput.focus();
        }
    });
    
    passwordInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            if (validateForm()) {
                form.submit();
            }
        }
    });
});

// Enhanced password toggle function
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'ri-eye-off-fill';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'ri-eye-fill';
    }
    
    // Brief focus animation
    passwordInput.focus();
}

// Keyboard accessibility for password toggle
document.addEventListener('keydown', function(e) {
    if (e.altKey && e.key === 'p') {
        e.preventDefault();
        togglePassword();
    }
});

// Smooth scroll for alerts
if (document.querySelector('.alert')) {
    document.querySelector('.alert').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'center' 
    });
}
</script>
@endsection
