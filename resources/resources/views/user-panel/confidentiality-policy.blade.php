@extends('layouts.master')
@section('title') {{$pageTitle}}  @endsection
@section('content')
	@component('components.breadcrumb')
		@slot('li_1') Pages @endslot
		@slot('title') {{$pageTitle}}  @endslot
	@endcomponent
	<div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <div class="flex-grow-1">
                        <h4 class="card-title mb-0 text-capitalize">
                            {{ $policy->heading_one }}
                        </h4>
                        <small class="mt-2"> last Update {{ $policy->updated_at->diffForHumans() }}</small>
                    </div>
                </div><!-- end card header -->

                <div class="card-body">
                    {!! $policy->content_one !!}
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
    </div>
@endsection
