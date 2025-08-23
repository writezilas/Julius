@extends('layouts.master')
@section('title') {{$pageTitle}} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') @lang('translation.dashboard') @endslot
@slot('title') {{$pageTitle}} @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                <a href="{{ route('admin.period.index') }}" class="btn btn-primary">
                    all trades
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.period.update', $period->id) }}" method="post">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Period Days</label>
                                <input type="number" class="form-control" name="days" value="{{ $period->days }}" autocomplete="none">
                                @error('days')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Percentage</label>
                                <input type="number" class="form-control" name="percentage" value="{{ $period->percentage }}">
                                @error('percentage')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label" style="padding-top: 35px;">
                                    <input type="checkbox" name="status" @checked($period->status == 1)>
                                    <span>Status</span>
                                </label>
                            </div>
                        </div>
                        @can('trade-periods-update')
                        <div class="col-lg-12">
                            <div class="form-group float-end">
                                <button type="submit" class="btn btn-primary">update trade period</button>
                            </div>
                        </div>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--end col-->
</div>

@endsection
@section('script')

@endsection
