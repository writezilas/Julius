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
                    <div class="d-flex gap-2">
                        <small class="text-muted align-self-center">
                            @php
                                try {
                                    $appTimezone = get_gs_value('app_timezone') ?? 'UTC';
                                } catch (Exception $e) {
                                    $appTimezone = 'UTC';
                                }
                            @endphp
                            Timezone: {{ $appTimezone }} ({{ now()->setTimezone($appTimezone)->format('H:i') }})
                        </small>
                        <a href="{{ route('admin.markets.index') }}" class="btn btn-primary">
                            All markets
                        </a>
                    </div>
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
                                    @php
                                        try {
                                            $appTimezone = get_gs_value('app_timezone') ?? 'UTC';
                                        } catch (Exception $e) {
                                            $appTimezone = 'UTC';
                                        }
                                        $defaultOpenTime = isset($market) ? $market->open_time : now()->setTimezone($appTimezone)->format('H:i');
                                    @endphp
                                    <input type="time" class="form-control" name="open_time" value="{{ $defaultOpenTime }}">
                                    @error('open_time')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                    <small class="form-text text-muted">Time in {{ $appTimezone }} timezone</small>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Close Time</label>
                                    @php
                                        $defaultCloseTime = isset($market) ? $market->close_time : now()->setTimezone($appTimezone)->addHour()->format('H:i');
                                    @endphp
                                    <input type="time" class="form-control" name="close_time" value="{{ $defaultCloseTime }}">
                                    @error('close_time')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                    <small class="form-text text-muted">Time in {{ $appTimezone }} timezone</small>
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
