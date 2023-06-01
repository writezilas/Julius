@extends('layouts.master')
@php($pageTitle = 'Refferal Code')
@section('title') {{$pageTitle}}  @endsection
@section('content')
	@component('components.breadcrumb')
		@slot('li_1') Pages @endslot
		@slot('title')  {{$pageTitle}} @endslot
	@endcomponent
	<div class="row justify-content-center mt-4">
        <div class="col-lg-5">
            <div class="text-center mb-4">
                <h4 class="fw-semibold fs-22">{{$pageTitle}}</h4>
                <p class="text-muted mb-2 fs-15">Copy Below link</p>
                <p class="text-muted mb-2 fs-15"><a href="javascript:;">{{url('/register?refferal_code='.auth()->user()->username)}}</a></p>
            </div>
        </div><!--end col-->
    </div><!--end row-->
@endsection
@section('script')
<script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
