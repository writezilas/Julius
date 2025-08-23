@extends('layouts.master')
@section('title') {{$pageTitle}}  @endsection
{{--@section('css')--}}
{{--    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />--}}
{{--    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css" />--}}
{{--@endsection--}}
@section('content')

@component('components.breadcrumb')
    @slot('li_1') @lang('translation.dashboard') @endslot
    @slot('title') {{$pageTitle}} @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="card-title mb-0"> {{ $pageTitle }} </h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roleCreateModal">
                    <i class="ri-add-box-fill"></i> New
                </button>
            </div>
            <div class="card-body">
                <table id="alternative-pagination" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                    <thead>
                    <tr>
                        <th>SR No.</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($roles as $key => $role)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $role->name }}</td>
                             <td>
                                 <a href="{{ route('admin.role.permission', $role->id) }}" class="btn btn-sm btn-soft-primary" >
                                     <i class="ri-settings-3-fill"></i>
                                 </a>
                                 <a href="{{ route('admin.role.delete', $role->id) }}" class="btn btn-sm btn-soft-danger delete_two">
                                     <i class="ri-delete-bin-5-line"></i>
                                 </a>
                                 <button class="btn btn-sm btn-soft-success" data-bs-toggle="modal" data-bs-target="#roleEditModal{{ $role->id }}">
                                     <i class="ri-edit-2-fill"></i>
                                 </button>
                             </td>
                        </tr>
                        <div class="modal fade" id="roleEditModal{{ $role->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Role create/edit</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('admin.role.update', $role->id) }}" method="post">
                                            @csrf
                                            @method('PATCH')
                                            <div class="form-group">
                                                <label>Role name</label>
                                                <input type="text" class="form-control" placeholder="Role name" name="name" value="{{ $role->name }}">
                                            </div>
                                            <div class="modal-footer mt-4">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!--end col-->
</div>

<div class="modal fade" id="roleCreateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Role create/edit</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.role.store') }}" method="post">
                    @csrf
                    <div class="form-group">
                        <label>Role name</label>
                        <input type="text" class="form-control" placeholder="Role name" name="name" value="{{ old('name') }}">
                    </div>
                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
