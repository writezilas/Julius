@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.signup')
@endsection
@section('content')

    <div class="auth-page-wrapper pt-5">
        <!-- auth page bg -->
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>

            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <!-- auth page content -->
        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div>
                                <a href="index" class="d-inline-block auth-logo">
                                    <img src="{{ URL::asset('assets/images/autobidder_light.png') }}" alt="" height="20">
                                </a>
                            </div>
                            <p class="mt-3 fs-15 fw-medium">Your Preferred Trading Partner</p>
                        </div>
                    </div>
                </div>
                <!-- end row -->

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4">

                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Create New Account</h5>
                                </div>
                                <div class="p-2 mt-4">
                                    <form class="needs-validation" novalidate method="POST"
                                        action="{{ route('register') }}" enctype="multipart/form-data">
                                        @csrf

                                        <div class="mb-3">
                                            <label for="name" class="form-label">Full Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                name="name" value="{{ old('name') }}" id="name"
                                                placeholder="Enter name" required>
                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter name
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="useremail" class="form-label">Email Address <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                name="email" value="{{ old('email') }}" id="useremail"
                                                placeholder="Enter email address" required>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter email
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="userphone" class="form-label">Phone Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('phone') is-invalid @enderror"
                                                name="phone" value="{{ old('phone') }}" id="userphone"
                                                placeholder="Enter phone number" required>
                                            @error('phone')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter phone
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                                name="username" value="{{ old('username') }}" id="username"
                                                placeholder="Enter username" required>
                                            @error('username')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter username
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="refferal" class="form-label">Refferal <span
                                                    class="text-dark">(Optional)</span></label>
                                            <input type="text" class="form-control @error('refferal') is-invalid @enderror"
                                                name="refferal" value="{{ old('refferal') ?? request()->get('refferal_code') }}" id="refferal"
                                                placeholder="Enter refferal" @if(request()->get('refferal_code')) readonly @endif>
                                            @error('refferal')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter refferal
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <label for="userpassword" class="form-label">Password <span
                                                    class="text-danger">*</span></label>
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror" name="password"
                                                id="userpassword" placeholder="Enter password" required>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter password
                                            </div>
                                        </div>
                                        <div class=" mb-4">
                                            <label for="input-password">Confirm Password</label>
                                            <input type="password"
                                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                                name="password_confirmation" id="input-password"
                                                placeholder="Enter Confirm Password" required>

                                            <div class="form-floating-icon">
                                                <i data-feather="lock"></i>
                                            </div>
                                        </div>
                                        <div class=" mb-4">
                                            <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                                name="avatar" id="input-avatar">
                                            @error('avatar')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="">
                                                <i data-feather="file"></i>
                                            </div>
                                        </div>
                                        <div class="text-start mt-2">
                                            <h5 class="text-primary">Busniss Profile</h5>
                                        </div>

                                        <div class="mb-3">
                                            <label for="business_account_id" class="form-label">Trading Account: <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control" name="business_account_id">
                                                <option value="1">MPESA</option>
                                                <option value="2">Till Number</option>
                                            </select>
                                            @error('business_account_id')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter business account
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="mpesa_no" class="form-label">MPesa No: <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('mpesa_no') is-invalid @enderror"
                                                name="mpesa_no" value="{{ old('mpesa_no') }}" id="mpesa_no"
                                                placeholder="Enter mpesa no" required>
                                            @error('mpesa_no')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter mpesa no
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="mpesa_name" class="form-label">MPesa Name: <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('mpesa_name') is-invalid @enderror"
                                                name="mpesa_name" value="{{ old('mpesa_name') }}" id="mpesa_name"
                                                placeholder="Enter mpesa name" required>
                                            @error('mpesa_name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter mpesa name
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="mpesa_till_no" class="form-label">MPesa Till No: <span
                                                    class="text-text">(Optional)</span></label>
                                            <input type="text" class="form-control @error('mpesa_till_no') is-invalid @enderror"
                                                name="mpesa_till_no" value="{{ old('mpesa_till_no') }}" id="mpesa_till_no"
                                                placeholder="Enter mpesa till no">
                                            @error('mpesa_till_no')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter till no
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="mpesa_till_name" class="form-label">MPesa Till Name: <span
                                                    class="text-text">(Optional)</span></label>
                                            <input type="text" class="form-control @error('mpesa_till_name') is-invalid @enderror"
                                                name="mpesa_till_name" value="{{ old('mpesa_till_name') }}" id="mpesa_till_name"
                                                placeholder="Enter mpesa till name">
                                            @error('mpesa_till_name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter till name
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="trading_category_id" class="form-label">Trading Category: <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control" name="trading_category_id">
                                                <option value="1">MPESA</option>
                                                <option value="2">Telkom</option>
                                                <option value="3">Airtel</option>
                                            </select>
                                            @error('trading_category_id')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            <div class="invalid-feedback">
                                                Please enter business account
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" required>
                                                <label class="form-check-label" for="auth-remember-check"><p class="mb-0 fs-12 text-muted fst-italic">I agreed, I have read and agree with the <a href="#"
                                                    class="text-primary text-decoration-underline fst-normal fw-medium">Terms
                                                    of Use</a></p></label>
                                                <div class="invalid-feedback">
                                                    Please agree
                                                </div>
                                            </div>
                                            
                                        </div>

                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit">Sign Up</button>
                                        </div>

                                        {{-- <div class="mt-4 text-center">
                                            <div class="signin-other-title">
                                                <h5 class="fs-13 mb-4 title text-muted">Create account with</h5>
                                            </div>

                                            <div>
                                                <button type="button"
                                                    class="btn btn-primary btn-icon waves-effect waves-light"><i
                                                        class="ri-facebook-fill fs-16"></i></button>
                                                <button type="button"
                                                    class="btn btn-danger btn-icon waves-effect waves-light"><i
                                                        class="ri-google-fill fs-16"></i></button>
                                                <button type="button"
                                                    class="btn btn-dark btn-icon waves-effect waves-light"><i
                                                        class="ri-github-fill fs-16"></i></button>
                                                <button type="button"
                                                    class="btn btn-info btn-icon waves-effect waves-light"><i
                                                        class="ri-twitter-fill fs-16"></i></button>
                                            </div>
                                        </div> --}}
                                    </form>

                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->

                        <div class="mt-4 text-center">
                            <p class="mb-0">Already have an account ? <a href="auth-signin-basic"
                                    class="fw-semibold text-primary text-decoration-underline"> Signin </a> </p>
                        </div>

                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <script>
                                document.write(new Date().getFullYear())
                            </script> {{env('APP_NAME', 'Auto Bidder')}}. Crafted with <i
                                    class="mdi mdi-heart text-danger"></i> <a href="#">#</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->
@endsection
@section('script')
    <script src="{{ URL::asset('assets/libs/particles.js/particles.js.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/particles.app.js') }}"></script>
    <script src="{{ URL::asset('assets/js/pages/form-validation.init.js') }}"></script>
@endsection
