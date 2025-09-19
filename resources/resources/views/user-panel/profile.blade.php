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
                            <img src="@if (Auth::user()->avatar != '') {{ asset(Auth::user()->avatar) }} @else{{ URL::asset('assets/images/users/avatar-1.jpg') }} @endif"
                                class="  rounded-circle avatar-xl img-thumbnail user-profile-image"
                                alt="user-profile-image">
                            <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                <input id="profile-img-file-input" type="file" class="profile-img-file-input">
                                <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                    <span class="avatar-title rounded-circle bg-light text-body">
                                        <i class="ri-camera-fill"></i>
                                    </span>
                                </label>
                            </div>
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
                            <form action="javascript:void(0);">
                                <div class="row">
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="business_account_id" class="form-label">Trading Account</label>
                                            <input type="text" class="form-control" id="business_account_id" business_account_id="business_account_id"
                                                placeholder="Enter your business_account_id" value="{{auth()->user()->business_account_id == 1 ? 'Mpesa' : 'Mpesa Till No'}}" readonly>
                                            @error('business_account_id')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="mpesa_no" class="form-label">Mpesa No:</label>
                                            <input type="text" class="form-control" id="mpesa_no" name="mpesa_no"
                                                placeholder="Enter your mpesa_no" value="{{json_decode(auth()->user()->business_profile,true)['mpesa_no']}}" readonly>
                                            @error('mpesa_no')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="mpesa_name" class="form-label">Mpesa Name</label>
                                            <input type="text" class="form-control" id="mpesa_name"  name="mpesa_name"
                                                placeholder="Enter your mpesa_name" value="{{json_decode(auth()->user()->business_profile,true)['mpesa_name']}}" readonly>
                                            @error('mpesa_name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="mpesa_till_no" class="form-label">Mpesa Till No</label>
                                            <input type="text" class="form-control" id="mpesa_till_no" name="mpesa_till_no"
                                                placeholder="Enter your mpesa_till_no" value="{{json_decode(auth()->user()->business_profile,true)['mpesa_till_no']}}" readonly>
                                            @error('mpesa_till_no')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="mpesa_till_name" class="form-label">Mpesa Till Name</label>
                                            <input type="text" class="form-control" id="mpesa_till_name" name="mpesa_till_name"
                                                placeholder="Enter your mpesa_till_name" value="{{json_decode(auth()->user()->business_profile,true)['mpesa_till_name']}}" readonly>
                                            @error('mpesa_till_name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="trading_category_id" class="form-label">Trading Category</label>
                                            <input type="text" class="form-control" id="trading_category_id" name="trading_category_id"
                                                placeholder="Enter your trading_category_id" value="{{auth()->user()->trade ? auth()->user()->trade->name : ''}}" readonly>
                                            @error('trading_category_id')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                </div>
                                <!--end row-->
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
                                            <input type="password" class="form-control" name="current_password"
                                                placeholder="Enter current password">
                                        </div>
                                        @error('current_password')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label class="form-label">New Password*</label>
                                            <input type="password" class="form-control" name="password"
                                                placeholder="Enter new password">
                                        </div>
                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label  class="form-label">Confirm Password*</label>
                                            <input type="password" class="form-control" name="password_confirmation"
                                                placeholder="Confirm password">
                                        </div>
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
@endsection
