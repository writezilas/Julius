@extends('layouts.master')
@php($pageTitle = auth()->user()->name)
@section('title')
    {{$pageTitle}}
@endsection
@section('content')
    <div class="position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg profile-setting-img">
            <img src="{{ URL::asset('assets/images/profile-bg.jpg') }}" class="profile-wid-img" alt="">

        </div>
    </div>

    <div class="row">
        <div class="col-xxl-3">
            <div class="card mt-n5">
                <div class="card-body p-4">
                    <div class="text-center">
                        <div class="profile-user position-relative d-inline-block mx-auto  mb-4">
                            <img src="{{ Auth::user()->avatar_url }}"
                                class="  rounded-circle avatar-xl img-thumbnail user-profile-image"
                                alt="user-profile-image">
                            {{-- <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                <input id="profile-img-file-input" type="file" class="profile-img-file-input">
                                <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                    <span class="avatar-title rounded-circle bg-light text-body">
                                        <i class="ri-camera-fill"></i>
                                    </span>
                                </label>
                            </div> --}}
                        </div>
                        <h5 class="fs-16 mb-1">{{Auth::user()->name}}</h5>
                        <p class="text-muted mb-0">{{Auth::user()->username}}</p>
                    </div>
                </div>
            </div>
            <!--end card-->
        </div>
        <!--end col-->
        <div class="col-xxl-9">
            <div class="card mt-xxl-n5">
                <div class="card-header">
                    <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab">
                                <i class="fas fa-home"></i>
                                Personal Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#bussinessProfile" role="tab">
                                <i class="fas fa-home"></i>
                                Bussiness Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">
                                <i class="far fa-user"></i>
                                Change Password
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">
                        <div class="tab-pane active" id="personalDetails" role="tabpanel">
                            <form action="{{ route('updateProfile', auth()->user()->id) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                <div class="row">
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="Enter your name" value="{{auth()->user()->name}}">
                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                placeholder="Enter your username" value="{{auth()->user()->username}}" disabled>
                                            @error('username')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="number" class="form-label">Phone
                                                Number</label>
                                            <input type="text" class="form-control" id="number" name="number"
                                                placeholder="Enter your phone number" value="{{auth()->user()->phone}}" disabled>
                                                @error('number')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email
                                                Address</label>
                                            <input type="email" class="form-control" id="email"
                                                placeholder="Enter your email" value="{{auth()->user()->email}}" name="email" disabled>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="refferal" class="form-label">Refferal Code</label>
                                            <input type="text" class="form-control" id="refferal"
                                                placeholder="Enter your refferal" value="{{auth()->user()->refferal_code}}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Avatar</label>
                                            <input type="file" class="form-control" name="avatar">
                                            @error('avatar')
                                            <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->

                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary">Updates</button>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                        <div class="tab-pane " id="bussinessProfile" role="tabpanel">
                            @if($canEditCoreProfile && $canEditSupplementaryFields)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> You can complete your business profile details. Once saved, these details cannot be modified later.
                            </div>
                            @elseif($canEditCoreProfile && !$canEditSupplementaryFields)
                            <div class="alert alert-info">
                                <i class="fas fa-edit"></i> You can update your core Mpesa details. Till details and Trading Category are already set and cannot be changed.
                            </div>
                            @elseif(!$canEditCoreProfile && $canEditSupplementaryFields)
                            <div class="alert alert-info">
                                <i class="fas fa-edit"></i> You can complete your Till details and Trading Category. Core Mpesa details are already set and cannot be changed.
                            </div>
                            @else
                            <div class="alert alert-warning">
                                <i class="fas fa-lock"></i> Your business profile has been completed and cannot be modified.
                            </div>
                            @endif
                            
                            <form action="{{ route('updateBusinessProfile', auth()->user()->id) }}" method="post">
                                @csrf
                                <div class="row">
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="business_account_id" class="form-label">Trading Account</label>
                                            @if($canEditCoreProfile)
                                                <select class="form-control @error('business_account_id') is-invalid @enderror" id="business_account_id" name="business_account_id" required>
                                                    <option value="">Select Trading Account</option>
                                                    <option value="1" {{ auth()->user()->business_account_id == 1 ? 'selected' : '' }}>Mpesa</option>
                                                    <option value="2" {{ auth()->user()->business_account_id == 2 ? 'selected' : '' }}>Mpesa Till No</option>
                                                </select>
                                            @else
                                                <input type="text" class="form-control" id="business_account_id" 
                                                    value="{{ auth()->user()->business_account_id ? (auth()->user()->business_account_id == 1 ? 'Mpesa' : 'Mpesa Till No') : 'Not Set' }}" readonly>
                                            @endif
                                            @error('business_account_id')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="mpesa_no" class="form-label">Mpesa No:</label>
                                            @if($canEditCoreProfile)
                                                <input type="text" class="form-control @error('mpesa_no') is-invalid @enderror" id="mpesa_no" name="mpesa_no"
                                                    placeholder="Enter your mpesa_no" value="{{ old('mpesa_no', auth()->user()->getBusinessProfileData()['mpesa_no']) }}" required>
                                            @else
                                                <input type="text" class="form-control" id="mpesa_no" name="mpesa_no"
                                                    value="{{ auth()->user()->getBusinessProfileData()['mpesa_no'] ?: 'Not Set' }}" readonly>
                                            @endif
                                            @error('mpesa_no')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="mpesa_name" class="form-label">Mpesa Name</label>
                                            @if($canEditCoreProfile)
                                                <input type="text" class="form-control @error('mpesa_name') is-invalid @enderror" id="mpesa_name"  name="mpesa_name"
                                                    placeholder="Enter your mpesa_name" value="{{ old('mpesa_name', auth()->user()->getBusinessProfileData()['mpesa_name']) }}" required>
                                            @else
                                                <input type="text" class="form-control" id="mpesa_name"  name="mpesa_name"
                                                    value="{{ auth()->user()->getBusinessProfileData()['mpesa_name'] ?: 'Not Set' }}" readonly>
                                            @endif
                                            @error('mpesa_name')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="mpesa_till_no" class="form-label">Mpesa Till No</label>
                                            @if($canEditSupplementaryFields)
                                                <input type="text" class="form-control @error('mpesa_till_no') is-invalid @enderror" id="mpesa_till_no" name="mpesa_till_no"
                                                    placeholder="Enter your mpesa_till_no" value="{{ old('mpesa_till_no', auth()->user()->getBusinessProfileData()['mpesa_till_no']) }}">
                                            @else
                                                <input type="text" class="form-control" id="mpesa_till_no" name="mpesa_till_no"
                                                    value="{{ auth()->user()->getBusinessProfileData()['mpesa_till_no'] ?: 'Not Set' }}" readonly>
                                            @endif
                                            @error('mpesa_till_no')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="mpesa_till_name" class="form-label">Mpesa Till Name</label>
                                            @if($canEditSupplementaryFields)
                                                <input type="text" class="form-control @error('mpesa_till_name') is-invalid @enderror" id="mpesa_till_name" name="mpesa_till_name"
                                                    placeholder="Enter your mpesa_till_name" value="{{ old('mpesa_till_name', auth()->user()->getBusinessProfileData()['mpesa_till_name']) }}">
                                            @else
                                                <input type="text" class="form-control" id="mpesa_till_name" name="mpesa_till_name"
                                                    value="{{ auth()->user()->getBusinessProfileData()['mpesa_till_name'] ?: 'Not Set' }}" readonly>
                                            @endif
                                            @error('mpesa_till_name')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="trading_category_id" class="form-label">Trading Category</label>
                                            @if($canEditSupplementaryFields)
                                                <select class="form-control @error('trading_category_id') is-invalid @enderror" id="trading_category_id" name="trading_category_id">
                                                    <option value="">Select Trading Category</option>
                                                    @foreach($trades as $trade)
                                                        <option value="{{ $trade->id }}" {{ (auth()->user()->trading_category_id == $trade->id || auth()->user()->trade_id == $trade->id) ? 'selected' : '' }}>{{ $trade->name }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" class="form-control" id="trading_category_id"
                                                    value="{{ auth()->user()->tradingCategory ? auth()->user()->tradingCategory->name : (auth()->user()->trade ? auth()->user()->trade->name : (auth()->user()->trading_category_id ? 'Category ID: ' . auth()->user()->trading_category_id : 'Not Set')) }}" readonly>
                                            @endif
                                            @error('trading_category_id')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                </div>
                                <!--end row-->
                                @if($canEditCoreProfile || $canEditSupplementaryFields)
                                <div class="col-lg-12">
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">
                                            @if($canEditCoreProfile && $canEditSupplementaryFields)
                                                Complete Business Profile
                                            @elseif($canEditCoreProfile)
                                                Complete Core Profile
                                            @elseif($canEditSupplementaryFields)
                                                Complete Till & Category Details
                                            @else
                                                Update Profile
                                            @endif
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </form>
                        </div>
                        <!--end tab-pane-->
                        <div class="tab-pane" id="changePassword" role="tabpanel">
                            <form action="{{ route('updatePassword', auth()->user()->id) }}" method="post">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-lg-4">
                                        <div>
                                            <label class="form-label">
                                                Current Password*</label>
                                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password"
                                                placeholder="Enter current password">
                                        </div>
                                        @error('current_password')
                                        <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label class="form-label">New Password*</label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password"
                                                placeholder="Enter new password">
                                        </div>
                                        @error('password')
                                        <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label  class="form-label">Confirm Password*</label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password_confirmation"
                                                placeholder="Confirm password">
                                        </div>
                                        @error('password')
                                        <span class="invalid-feedback d-block" role="alert">
                                        <strong>The password confirmation does not match.</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <!--end col-->
                                    @if (Route::has('password.request'))
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <a href="{{ route('password.request') }}"
                                                    class="link-primary text-decoration-underline">Forgot
                                                    Password ?</a>
                                            </div>
                                        </div>
                                    @endif
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-success">Change
                                                Password</button>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>

                        </div>
                        <!--end tab-pane-->
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>
    <!--end row-->
@endsection
@section('script')
    <script src="{{ URL::asset('assets/js/pages/profile-setting.init.js') }}"></script>
    <script>
        // Password change form validation
        document.addEventListener('DOMContentLoaded', function() {
            const passwordForm = document.querySelector('#changePassword form');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    const currentPassword = document.querySelector('input[name="current_password"]').value;
                    const newPassword = document.querySelector('input[name="password"]').value;
                    const confirmPassword = document.querySelector('input[name="password_confirmation"]').value;
                    
                    // Basic validation
                    if (!currentPassword) {
                        alert('Please enter your current password');
                        e.preventDefault();
                        return false;
                    }
                    
                    if (!newPassword) {
                        alert('Please enter a new password');
                        e.preventDefault();
                        return false;
                    }
                    
                    if (newPassword.length < 6) {
                        alert('New password must be at least 6 characters long');
                        e.preventDefault();
                        return false;
                    }
                    
                    if (newPassword !== confirmPassword) {
                        alert('Password confirmation does not match the new password');
                        e.preventDefault();
                        return false;
                    }
                    
                    if (currentPassword === newPassword) {
                        alert('New password must be different from current password');
                        e.preventDefault();
                        return false;
                    }
                    
                    return true;
                });
            }
        });

        // Business profile form validation
        const businessForm = document.querySelector('#bussinessProfile form');
        if (businessForm) {
            businessForm.addEventListener('submit', function(e) {
                const businessAccountId = document.querySelector('select[name="business_account_id"]');
                const mpesaNo = document.querySelector('input[name="mpesa_no"]');
                const mpesaName = document.querySelector('input[name="mpesa_name"]');
                const mpesaTillNo = document.querySelector('input[name="mpesa_till_no"]');
                const mpesaTillName = document.querySelector('input[name="mpesa_till_name"]');
                const tradingCategoryId = document.querySelector('select[name="trading_category_id"]');
                
                let hasEditableFields = false;
                let confirmMessage = '';
                
                // Check core fields validation
                if (businessAccountId && !businessAccountId.disabled) {
                    hasEditableFields = true;
                    if (!businessAccountId.value) {
                        alert('Please select a trading account');
                        e.preventDefault();
                        return false;
                    }
                }
                
                if (mpesaNo && !mpesaNo.readOnly) {
                    hasEditableFields = true;
                    if (!mpesaNo.value.trim()) {
                        alert('Please enter your M-Pesa number');
                        e.preventDefault();
                        return false;
                    }
                    
                    if (mpesaNo.value.length < 10) {
                        alert('M-Pesa number must be at least 10 digits');
                        e.preventDefault();
                        return false;
                    }
                }
                
                if (mpesaName && !mpesaName.readOnly) {
                    hasEditableFields = true;
                    if (!mpesaName.value.trim()) {
                        alert('Please enter your M-Pesa name');
                        e.preventDefault();
                        return false;
                    }
                }
                
                // Check supplementary fields
                if (mpesaTillNo && !mpesaTillNo.readOnly) {
                    hasEditableFields = true;
                }
                
                if (mpesaTillName && !mpesaTillName.readOnly) {
                    hasEditableFields = true;
                }
                
                if (tradingCategoryId && !tradingCategoryId.disabled) {
                    hasEditableFields = true;
                    // Trading category is optional, no validation needed
                }
                
                // Don't submit if no editable fields
                if (!hasEditableFields) {
                    alert('No fields are available for editing.');
                    e.preventDefault();
                    return false;
                }
                
                // Set confirmation message based on what's being edited
                const hasEditableCoreFields = (mpesaNo && !mpesaNo.readOnly) || (mpesaName && !mpesaName.readOnly);
                const hasEditableSupplementaryFields = (mpesaTillNo && !mpesaTillNo.readOnly) || (mpesaTillName && !mpesaTillName.readOnly) || (tradingCategoryId && !tradingCategoryId.disabled);
                
                if (hasEditableCoreFields && hasEditableSupplementaryFields) {
                    confirmMessage = 'Are you sure you want to complete your business profile? Once saved, these details cannot be modified later.';
                } else if (hasEditableCoreFields) {
                    confirmMessage = 'Are you sure you want to complete your core Mpesa details? Once saved, they cannot be modified later.';
                } else if (hasEditableSupplementaryFields) {
                    confirmMessage = 'Are you sure you want to complete your Till and Category details? Once saved, they cannot be modified later.';
                } else {
                    confirmMessage = 'Are you sure you want to save these changes?';
                }
                
                // Confirm before submission
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
        }
    </script>
@endsection
