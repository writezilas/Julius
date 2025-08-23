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
                @can('staff-create')
                <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
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
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            @canAny(['staff-edit', 'staff-delete'])
                            <th>Action</th>
                            @endcanAny
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$user->name}}</td>
                            <td>{{$user->email}}</td>
                            <td>{{$user->role->name}}</td>
                            <td>{{\Carbon\Carbon::parse($user->created_at)}}</td>
                            @canAny(['staff-edit', 'staff-delete'])
                            <td>
                                @can('staff-edit')
                                <a href="{{ route('admin.staff.edit', $user->id) }}" class="btn btn-sm btn-soft-success">
                                    <i class="ri-edit-2-fill"></i>
                                </a>
                                @endcan
                                @can('staff-delete')
                                <a href="{{ route('admin.staff.delete', $user->id) }}" class="btn btn-sm btn-soft-danger delete_two">
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
