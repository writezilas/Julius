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
                @can('announcement-create')
                <a href="{{ route('announcement.create') }}" class="btn btn-primary">
                    <i class="ri-add-box-fill"></i> New
                </a>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="alternative-pagination" class="table dt-responsive align-middle table-hover table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Sl</th>
                                <th>title</th>
                                <th>Sort description</th>
                                <th>Description</th>
                                <th>Created at</th>
                                @canAny(['announcement-edit', 'announcement-delete'])
                                <th>Action</th>
                                @endcanAny
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($announcements as $announcement)

                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $announcement->title }}</td>
                                <td>{{ $announcement->excerpt }}</td>
                                <td>{!! $announcement->description !!}</td>
                                <td>{{ $announcement->created_at->diffForHumans() }}</td>
                                @canAny(['announcement-edit', 'announcement-delete'])
                                <td>
                                    @can('announcement-edit')
                                    <a href="{{ route('announcement.edit', $announcement->id) }}" class="btn btn-sm btn-success">
                                        Edit
                                    </a>
                                    @endcan
                                    @can('announcement-delete')
                                    <a href="{{ route('announcement.delete', $announcement->id) }}" class="btn btn-sm btn-danger delete_two">
                                        delete
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
    </div>
    <!--end col-->
</div>

@endsection
@section('script')

@endsection
