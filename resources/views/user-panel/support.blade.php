@extends('layouts.master')
@php($pageTitle = __('translation.support'))
@section('title', $pageTitle)
@section('content')
	 <div class="row justify-content-center mt-4">
        <div class="col-lg-5">
            <div class="text-center mb-4">
                <h4 class="fw-semibold fs-22">Official Channels</h4>
                <p class="text-muted mb-2 fs-15">Join our Telegram channel here:</p>
                <p class="text-muted mb-2 fs-15"><a href="https://t.me/autobidder_live">Telegram Channel</a></p>
                <p class="text-muted mb-2 fs-15">Join our Telegram Group here:</p>
                <p class="text-muted mb-2 fs-15"><a href="h#">Telegram Group</a></p>
                
            </div>
        </div><!--end col-->
        
    </div><!--end row-->
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Talk to Us</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div class="live-preview">
                        <div class="row gy-4">
                        	<form class="needs-validation" novalidate method="POST" action="{{route('supports.store')}}">
                                @csrf
	                            <div class="col-xxl-12 col-md-12 mb-3">
	                                <div class="form-floating">
	                                    <input type="text" class="form-control" id="first_name" name="first_name"
	                                        placeholder="Enter your firstname" required>
	                                    <label for="first_name">First Name <span
                                                    class="text-danger">*</span></label>
                                        @error('first_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
	                                </div>
	                            </div>
	                            <div class="col-xxl-12 col-md-12 mb-3">
	                                <div class="form-floating">
	                                    <input type="text" class="form-control" id="last_name" name="last_name"
	                                        placeholder="Enter your lastname" required>
	                                    <label for="last_name">Last Name <span
                                                    class="text-danger">*</span></label>
                                        @error('last_name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
	                                </div>
	                            </div>
	                            <div class="col-xxl-12 col-md-12 mb-3">
	                                <div class="form-floating">
	                                    <input type="email" class="form-control" id="email" name="email"
	                                        placeholder="Enter your email" required>
	                                    <label for="email">Email <span
                                                    class="text-danger">*</span></label>
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
	                                </div>
	                            </div>
	                            <div class="col-xxl-12 col-md-12 mb-3">
	                                <div class="form-floating">
	                                    <input type="text" class="form-control" id="username" name="username"
	                                        placeholder="Enter your username">
	                                    <label for="username">Username <span
                                                    class="text-dark">(Optional)</span></label>
                                        @error('username')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
	                                </div>
	                            </div>

	                            <div class="col-xxl-12 col-md-12 mb-3">
	                                <div class="form-floating">
	                                    <input type="number" class="form-control" id="telephone" name="telephone"
	                                        placeholder="Enter your telephone" required>
	                                    <label for="telephone">Telephone <span
                                                    class="text-danger">*</span></label>
                                        @error('telephone')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
	                                </div>
	                            </div>
	                            <div class="col-xxl-12 col-md-12 mb-3">
	                                <div class="form-floating">
	                                    <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
	                                    <label for="message">Message <span
                                                    class="text-danger">*</span></label>
                                        @error('message')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        <div id="messageHelpBlock" class="form-text">
                                        	Limit word to 150 words
                                        </div>
	                                </div>
	                            </div>

	                             <div class="mt-4">
                                    <button class="btn btn-success w-100" type="submit">Submit</button>
                                </div>
	                          </form>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>


@endsection

@section('script')
	<script src="{{ URL::asset('assets/js/pages/form-validation.init.js') }}"></script>
@endsection
