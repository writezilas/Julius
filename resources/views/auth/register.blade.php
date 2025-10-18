@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.signup')
@endsection

@section('css')
<style>
    /* Enhanced Register Page Styles - Consistent with Login */
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
    
    .section-title {
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 600;
        font-size: 1.2rem;
        margin: 1.5rem 0 1rem 0;
        text-align: left;
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
    
    .form-floating select.form-control {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    
    .form-floating.file-input .form-control {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    
    .custom-checkbox {
        display: flex;
        align-items: flex-start;
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
        margin-top: 2px;
        flex-shrink: 0;
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
        font-size: 0.9rem;
        cursor: pointer;
        margin: 0;
        user-select: none;
        line-height: 1.4;
    }
    
    .signup-btn {
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
    
    .signup-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .signup-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }
    
    .signup-btn:hover::before {
        left: 100%;
    }
    
    .signup-btn:active {
        transform: translateY(0);
    }
    
    .signup-btn:focus {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
    }
    
    .signup-btn .btn-loading {
        display: none;
    }
    
    .signup-btn.loading .btn-text {
        display: none;
    }
    
    .signup-btn.loading .btn-loading {
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
        
        .signup-btn {
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
    
    /* Animation for form elements */
    .form-floating {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.6s ease forwards;
    }
    
    .form-floating:nth-child(1) { animation-delay: 0.1s; }
    .form-floating:nth-child(2) { animation-delay: 0.15s; }
    .form-floating:nth-child(3) { animation-delay: 0.2s; }
    .form-floating:nth-child(4) { animation-delay: 0.25s; }
    .form-floating:nth-child(5) { animation-delay: 0.3s; }
    .form-floating:nth-child(6) { animation-delay: 0.35s; }
    .custom-checkbox { animation-delay: 0.4s; }
    .signup-btn { animation-delay: 0.45s; }
    
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
                                <img src="{{ URL::asset('assets/images/autobidder_logo.svg') }}" alt="Auto Bidder Logo" height="40" class="autobidder-logo auth-logo-img">
                            </a>
                        </div>
                        <p class="mt-3 fs-16 fw-medium opacity-75">Your Preferred Trading Partner</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-7">
                    <div class="card auth-card mt-4">
                        <div class="card-body">
                            <div class="mb-4">
                                <h2 class="welcome-title">Create New Account</h2>
                                <p class="welcome-subtitle">Join us today and start your trading journey</p>
                            </div>
                            <form class="needs-validation" novalidate method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registerForm">
                                @csrf

                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror"
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           id="name"
                                           placeholder="Enter your full name" 
                                           required
                                           autofocus>
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    @error('name')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-floating">
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror"
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           id="useremail"
                                           placeholder="Enter your email address" 
                                           required>
                                    <label for="useremail">Email Address <span class="text-danger">*</span></label>
                                    @error('email')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-floating">
                                    <input type="tel" 
                                           class="form-control @error('phone') is-invalid @enderror"
                                           name="phone" 
                                           value="{{ old('phone') }}" 
                                           id="userphone"
                                           placeholder="Enter your phone number" 
                                           required>
                                    <label for="userphone">Phone Number <span class="text-danger">*</span></label>
                                    @error('phone')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control @error('username') is-invalid @enderror"
                                           name="username" 
                                           value="{{ old('username') }}" 
                                           id="username"
                                           placeholder="Enter your username" 
                                           required>
                                    <label for="username">Username <span class="text-danger">*</span></label>
                                    @error('username')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                
                                <!-- Hidden field to pass referral code from URL parameter -->
                                @if(request()->get('refferal_code'))
                                <input type="hidden" name="refferal" value="{{ request()->get('refferal_code') }}">
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information"></i> You were referred by: <strong>{{ request()->get('refferal_code') }}</strong>
                                </div>
                                @endif

                                <div class="form-floating">
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror" 
                                           name="password"
                                           id="userpassword" 
                                           placeholder="Enter your password" 
                                           required>
                                    <label for="userpassword">Password <span class="text-danger">*</span></label>
                                    @error('password')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-floating">
                                    <input type="password"
                                           class="form-control @error('password_confirmation') is-invalid @enderror"
                                           name="password_confirmation" 
                                           id="input-password"
                                           placeholder="Confirm your password" 
                                           required>
                                    <label for="input-password">Confirm Password <span class="text-danger">*</span></label>
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-floating file-input">
                                    <input type="file" 
                                           class="form-control @error('avatar') is-invalid @enderror"
                                           name="avatar" 
                                           id="input-avatar"
                                           accept="image/*">
                                    <label for="input-avatar">Profile Picture <span class="text-muted">(Optional)</span></label>
                                    @error('avatar')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                <h3 class="section-title">Business Profile</h3>

                                <div class="form-floating">
                                    <select class="form-control @error('business_account_id') is-invalid @enderror" name="business_account_id" id="business_account_id">
                                        <option value="1" {{ old('business_account_id') == '1' ? 'selected' : '' }}>MPESA</option>
                                        <option value="2" {{ old('business_account_id') == '2' ? 'selected' : '' }}>Till Number</option>
                                    </select>
                                    <label for="business_account_id">Trading Account <span class="text-danger">*</span></label>
                                    @error('business_account_id')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control @error('mpesa_no') is-invalid @enderror"
                                           name="mpesa_no" 
                                           value="{{ old('mpesa_no') }}" 
                                           id="mpesa_no"
                                           placeholder="Enter mpesa number" 
                                           required>
                                    <label for="mpesa_no">MPesa Number <span class="text-danger">*</span></label>
                                    @error('mpesa_no')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control @error('mpesa_name') is-invalid @enderror"
                                           name="mpesa_name" 
                                           value="{{ old('mpesa_name') }}" 
                                           id="mpesa_name"
                                           placeholder="Enter mpesa account name" 
                                           required>
                                    <label for="mpesa_name">MPesa Account Name <span class="text-danger">*</span></label>
                                    @error('mpesa_name')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control @error('mpesa_till_no') is-invalid @enderror"
                                           name="mpesa_till_no" 
                                           value="{{ old('mpesa_till_no') }}" 
                                           id="mpesa_till_no"
                                           placeholder="Enter mpesa till number">
                                    <label for="mpesa_till_no">MPesa Till Number <span class="text-muted">(Optional)</span></label>
                                    @error('mpesa_till_no')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control @error('mpesa_till_name') is-invalid @enderror"
                                           name="mpesa_till_name" 
                                           value="{{ old('mpesa_till_name') }}" 
                                           id="mpesa_till_name"
                                           placeholder="Enter mpesa till name">
                                    <label for="mpesa_till_name">MPesa Till Name <span class="text-muted">(Optional)</span></label>
                                    @error('mpesa_till_name')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="form-floating">
                                    @php
                                        $trades = \App\Models\Trade::where('status', 1)->get();
                                    @endphp
                                    <select class="form-control @error('trade_id') is-invalid @enderror" name="trade_id" id="trade_id">
                                        @if($trades->count() > 0)
                                            @foreach($trades as $trade)
                                                <option value="{{ $trade->id }}" {{ old('trade_id') == $trade->id ? 'selected' : '' }}>
                                                    {{ $trade->name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">No trading categories available</option>
                                        @endif
                                    </select>
                                    <label for="trade_id">Trading Category @if($trades->count() > 0)<span class="text-danger">*</span>@else<span class="text-muted">(Not Available)</span>@endif</label>
                                    @if($trades->count() == 0)
                                        <div class="text-muted small mt-2">
                                            <i class="mdi mdi-information"></i> Trading categories will be available soon. You can still register and select a category later.
                                        </div>
                                    @endif
                                    @error('trade_id')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                    @error('trading_category_id')
                                        <div class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="custom-checkbox">
                                    <input type="checkbox" id="termsCheck" name="terms" value="1" required>
                                    <label for="termsCheck">
                                        I have read and agree to the <a href="#" class="text-decoration-none" style="color: #667eea; font-weight: 600;">Terms of Use</a>
                                    </label>
                                </div>

                                <button type="submit" class="btn signup-btn" id="signupBtn">
                                    <span class="btn-text">Create Account</span>
                                    <span class="btn-loading">
                                        <span class="spinner"></span>
                                    </span>
                                </button>

                            </form>
                        </div>
                    </div>

                    <div class="signin-link">
                        <p class="mb-0">Already have an account? <a href="{{ route('login') }}">Sign in here</a></p>
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
// Enhanced register page functionality
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const signupBtn = document.getElementById('signupBtn');
    const inputs = {
        name: document.getElementById('name'),
        email: document.getElementById('useremail'),
        phone: document.getElementById('userphone'),
        username: document.getElementById('username'),
        password: document.getElementById('userpassword'),
        confirmPassword: document.getElementById('input-password'),
        termsCheck: document.getElementById('termsCheck')
    };
    
    // Form validation
    function validateForm() {
        let isValid = true;
        
        // Name validation
        if (!inputs.name.value.trim()) {
            showFieldError(inputs.name, 'Full name is required');
            isValid = false;
        } else if (inputs.name.value.trim().length < 2) {
            showFieldError(inputs.name, 'Name must be at least 2 characters');
            isValid = false;
        } else {
            clearFieldError(inputs.name);
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!inputs.email.value.trim()) {
            showFieldError(inputs.email, 'Email is required');
            isValid = false;
        } else if (!emailRegex.test(inputs.email.value.trim())) {
            showFieldError(inputs.email, 'Please enter a valid email address');
            isValid = false;
        } else {
            clearFieldError(inputs.email);
        }
        
        // Phone validation
        const phoneRegex = /^[+]?[0-9]{10,15}$/;
        if (!inputs.phone.value.trim()) {
            showFieldError(inputs.phone, 'Phone number is required');
            isValid = false;
        } else if (!phoneRegex.test(inputs.phone.value.trim().replace(/\s+/g, ''))) {
            showFieldError(inputs.phone, 'Please enter a valid phone number');
            isValid = false;
        } else {
            clearFieldError(inputs.phone);
        }
        
        // Username validation
        if (!inputs.username.value.trim()) {
            showFieldError(inputs.username, 'Username is required');
            isValid = false;
        } else if (inputs.username.value.trim().length < 3) {
            showFieldError(inputs.username, 'Username must be at least 3 characters');
            isValid = false;
        } else {
            clearFieldError(inputs.username);
        }
        
        // Password validation
        if (!inputs.password.value) {
            showFieldError(inputs.password, 'Password is required');
            isValid = false;
        } else if (inputs.password.value.length < 8) {
            showFieldError(inputs.password, 'Password must be at least 8 characters');
            isValid = false;
        } else {
            clearFieldError(inputs.password);
        }
        
        // Confirm password validation
        if (!inputs.confirmPassword.value) {
            showFieldError(inputs.confirmPassword, 'Password confirmation is required');
            isValid = false;
        } else if (inputs.password.value !== inputs.confirmPassword.value) {
            showFieldError(inputs.confirmPassword, 'Passwords do not match');
            isValid = false;
        } else {
            clearFieldError(inputs.confirmPassword);
        }
        
        // Terms validation
        if (!inputs.termsCheck.checked) {
            showFieldError(inputs.termsCheck, 'You must agree to the terms of use');
            isValid = false;
        } else {
            clearFieldError(inputs.termsCheck);
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
    
    // Real-time validation
    inputs.email.addEventListener('blur', function() {
        if (this.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.value.trim())) {
                showFieldError(this, 'Please enter a valid email address');
            } else {
                clearFieldError(this);
            }
        }
    });
    
    inputs.confirmPassword.addEventListener('blur', function() {
        if (this.value && inputs.password.value) {
            if (inputs.password.value !== this.value) {
                showFieldError(this, 'Passwords do not match');
            } else {
                clearFieldError(this);
            }
        }
    });
    
    inputs.password.addEventListener('input', function() {
        if (inputs.confirmPassword.value) {
            if (this.value !== inputs.confirmPassword.value) {
                showFieldError(inputs.confirmPassword, 'Passwords do not match');
            } else {
                clearFieldError(inputs.confirmPassword);
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
        signupBtn.classList.add('loading');
        signupBtn.disabled = true;
        
        // Add a timeout to prevent infinite loading
        setTimeout(function() {
            if (signupBtn.classList.contains('loading')) {
                signupBtn.classList.remove('loading');
                signupBtn.disabled = false;
            }
        }, 15000); // Longer timeout for registration
    });
    
    // Enhanced keyboard navigation
    const formInputs = [inputs.name, inputs.email, inputs.phone, inputs.username, inputs.password, inputs.confirmPassword];
    
    formInputs.forEach((input, index) => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (index < formInputs.length - 1) {
                    formInputs[index + 1].focus();
                } else {
                    // Focus on first business profile field
                    document.getElementById('business_account_id').focus();
                }
            }
        });
    });
});
</script>
@endsection
