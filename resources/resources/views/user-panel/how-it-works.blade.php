@extends('layouts.master')
@php($pageTitle = 'How it works')
@section('title') {{$pageTitle}}  @endsection
@section('content')
	@component('components.breadcrumb')
		@slot('li_1') Pages @endslot
		@slot('title') {{$pageTitle}}  @endslot
	@endcomponent
	<div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <div class="flex-grow-1">
                        <h4 class="card-title mb-0">
                            {{ $policy->heading_one }}
                        </h4>
                    </div>
                </div><!-- end card header -->

                <div class="card-body">
                    {!! $policy->content_one !!}
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <div class="flex-grow-1">
                        <h4 class="card-title mb-0">
                            {{ $policy->heading_two }}
                        </h4>
                    </div>
                </div><!-- end card header -->

                <div class="card-body">
                   {!! $policy->content_two !!}
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
    </div>
@endsection
