@extends('layouts.master')
@section('title') {{$pageTitle}}  @endsection
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
                    <a href="{{ route('admin.markets.index') }}" class="btn btn-primary">
                        All markets
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ isset($market) ? route('admin.markets.update', $market->id) : route('admin.markets.store') }}" method="post">
                        @csrf
                        @if(isset($market))
                        @method('put')
                        @endif
                        <div class="row">

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Open Time</label>
                                    <input type="time" class="form-control" name="open_time" value="{{ (@$market->open_time ?? now()->format('H:i'))  }}">
                                    @error('open_time')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Close Time</label>
                                    <input type="time" class="form-control" name="close_time" value="{{ (@$market->close_time ?? now()->format('H:i'))  }}">
                                    @error('close_time')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                           
                            @can('market-update')
                            <div class="col-lg-12">
                                <div class="form-group float-end">
                                    <button type="submit" class="btn btn-primary">{{isset($market) ? 'Update' : 'Create'}} market</button>
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
