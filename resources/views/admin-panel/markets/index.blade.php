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
                <div>
                    <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                    @php
                        try {
                            $appTimezone = get_gs_value('app_timezone') ?? 'UTC';
                        } catch (Exception $e) {
                            $appTimezone = 'UTC';
                        }
                    @endphp
                    <small class="text-muted">All times shown in {{ $appTimezone }} timezone</small>
                </div>
                @can('market-create')
                <a href="{{ route('admin.markets.create') }}" class="btn btn-primary">
                    <i class="ri-add-box-fill"></i> New
                </a>
                @endcan
            </div>
            <div class="card-body">
                <table id="alternative-pagination" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>SR No.</th>
                            <th>Open Time</th>
                            <th>Close Time</th>
                            <th>Created At</th>
                            @canAny(['market-edit', 'market-delete'])
                            <th>Action</th>
                            @endcanAny
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($markets as $market)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{$market->open_time}} 
                                @php
                                    $isMarketOpen = false;
                                    try {
                                        $isMarketOpen = $market->isOpen();
                                    } catch (Exception $e) {
                                        // Silently handle any timezone or other errors
                                        $isMarketOpen = false;
                                    }
                                @endphp
                                @if($isMarketOpen)
                                    <span class="badge bg-success">Open Now</span>
                                @endif
                            </td>
                            <td>{{$market->close_time}}</td>
                            <td>{{\Carbon\Carbon::parse($market->created_at)}}</td>
                            @canAny(['market-edit', 'market-delete'])
                            <td>
                                @can('market-edit')
                                <a href="{{ route('admin.markets.edit', $market->id) }}" class="btn btn-sm btn-soft-success">
                                    <i class="ri-edit-2-fill"></i>
                                </a>
                                @endcan
                                @can('market-delete')
                                <a href="{{ route('admin.markets.delete', $market->id) }}" class="btn btn-sm btn-soft-danger delete_two">
                                    <i class="ri-delete-bin-5-line"></i>
                                </a>
                                @endcan
                            </td>
                            @endcanAny
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!--end col-->
</div>

@endsection
@section('script')

@endsection
