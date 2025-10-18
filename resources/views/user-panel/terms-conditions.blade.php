@extends('layouts.master')
@section('title') {{$pageTitle}}  @endsection
@section('content')
	<div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <div class="flex-grow-1">
                        <h4 class="card-title mb-0 text-capitalize">
                            {{ $policy ? $policy->heading_one : $pageTitle }}
                        </h4>
                        @if($policy)
                            <small class="mt-2"> last Update {{ $policy->updated_at->diffForHumans() }}</small>
                        @endif
                    </div>
                </div><!-- end card header -->

                <div class="card-body">
                    @if($policy)
                        {!! $policy->content_one !!}
                        @if($policy->heading_two && $policy->content_two)
                        <div class="mt-4">
                            <h5>{{ $policy->heading_two }}</h5>
                            {!! $policy->content_two !!}
                        </div>
                        @endif
                    @else
                        <p class="text-muted">Terms and conditions content is not available at the moment. Please contact support for more information.</p>
                    @endif
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
    </div>
@endsection
