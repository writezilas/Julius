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
                                <small class="form-text text-info mt-2">
                                    <i class="fas fa-info-circle"></i> <strong>Note:</strong> This setting applies to new user registrations only. 
                                    Users who have already registered will receive referral bonuses based on the amount that was active when they registered.
                                    This ensures fair bonus distribution regardless of future changes to this setting.
                                </small>
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
                        <div class="col-6">
                            <div class="form-group">
                                <label>Admin Email *</label>
                                <input type="email" name="settings[admin_email]" class="form-control" value="{{ @$gs['admin_email'] ?? '' }}" placeholder="admin@example.com">
                                @error('settings.admin_email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                                <small class="form-text text-muted">This email will be used for admin notifications and system communications</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Application Timezone *</label>
                                <select name="settings[app_timezone]" class="form-control">
                                    @php
                                        $timezones = timezone_identifiers_list();
                                        try {
                                            $current_timezone = @$gs['app_timezone'] ?? 'UTC';
                                        } catch (Exception $e) {
                                            $current_timezone = 'UTC';
                                        }
                                    @endphp
                                    @foreach($timezones as $timezone)
                                        <option value="{{ $timezone }}" {{ $timezone === $current_timezone ? 'selected' : '' }}>
                                            {{ $timezone }} ({{ now()->setTimezone($timezone)->format('H:i') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('settings.app_timezone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                                <small class="form-text text-muted">This timezone will be used for all market opening/closing times</small>
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
