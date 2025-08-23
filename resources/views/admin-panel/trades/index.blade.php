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
                @can('trade-create')
                <a href="{{ route('admin.trade.create') }}" class="btn btn-primary">
                    <i class="ri-add-box-fill"></i> New
                </a>
                @endcan
            </div>
            <div class="card-body">
                <table id="alternative-pagination" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>SR No.</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Created At</th>
                            @canAny(['trade-edit', 'trade-delete'])
                            <th>Action</th>
                            @endcanAny
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trades as $trade)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{$trade->name}}</td>
                            <td>{{$trade->price}}</td>
                            <td>{{\Carbon\Carbon::parse($trade->created_at)}}</td>
                            @canAny(['trade-edit', 'trade-delete'])
                            <td>
                                @can('trade-edit')
                                <a href="{{ route('admin.trade.edit', $trade->id) }}" class="btn btn-sm btn-soft-success">
                                    <i class="ri-edit-2-fill"></i>
                                </a>
                                @endcan
                                @can('trade-delete')
                                <a href="{{ route('admin.trade.delete', $trade->id) }}" class="btn btn-sm btn-soft-danger delete_two">
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
