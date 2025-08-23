@extends('layouts.master')
@section('title') {{$pageTitle}} @endsection
@section('content')

@component('components.breadcrumb')
@slot('li_1') @lang('translation.dashboard') @endslot
@slot('title') {{$pageTitle}} @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12 ">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="card-title mb-0"> {{ $pageTitle }} </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.general-setting') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Reffaral Bonus *</label>
                                <input type="number" name="settings[reffaral_bonus]" class="form-control" value="{{ @$gs['reffaral_bonus'] }}">
                                @error('settings.refferal_bonus')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Bought time *</label>
                                <input type="number" name="settings[bought_time]" class="form-control" value="{{ (@$gs['bought_time'] ?? "1440") }}">
                                @error('settings.bought_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        {{-- <div class="col-6">
                            <div class="form-group">
                                <label>Open Market *</label>
                                <input type="time" name="settings[open_market]" class="form-control" value="{{ (@$gs['open_market'] ?? now()->format('H:i')) }}">
                                @error('settings.open_market')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Close Market *</label>
                                <input type="time" name="settings[close_market]" class="form-control" value="{{ (@$gs['close_market'] ?? now()->format('H:i')) }}">
                                @error('settings.close_market')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div> --}}
                    </div>
                    @can('general-setting-update')
                    <div class="mt-3">
                        <input type="submit" value="Submit" class="btn btn-primary float-end">
                    </div>
                    @endcan
                </form>
            </div>
        </div>
    </div>
    <!--end col-->
</div>


@endsection

@section('script')
<script>

</script>
@endsection
