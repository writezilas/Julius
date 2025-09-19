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
                </div>
                <div class="card-body">
                    <table id="alternative-pagination" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                        <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Phone no</th>
                            <th>Created at</th>
                            <th>Status</th>
                            <th>View</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($supports as $support)

                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $support->name }}</td>
                                <td>{{ $support->email }}</td>
                                <td>{{ $support->username }}</td>
                                <td>{{ $support->number }}</td>
                                <td>{{ $support->created_at->diffForHumans() }}</td>
                                <td>
                                    @if($support->status === 0)
                                        <span class="badge bg-info text-dark">
                                            Running
                                        </span>
                                    @else
                                        <span class="badge bg-success ">
                                            Closed
                                        </span>
                                    @endif
                                </td>
                                <td>
                                   <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#supportModal{{ $support->id }}">
                                       View
                                   </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="supportModal{{ $support->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title" id="exampleModalLabel">
                                                        Support request from {{ $support->first_name }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-bordered">
                                                        <tbody>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Name</b>
                                                                </td>
                                                                <td>{{ $support->name }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Email</b>
                                                                </td>
                                                                <td>{{ $support->email }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Phone</b>
                                                                </td>
                                                                <td>{{ $support->number }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Username</b>
                                                                </td>
                                                                <td>{{ $support->username }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Created at</b>
                                                                </td>
                                                                <td>{{ $support->created_at->diffForHumans() }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Status</b>
                                                                </td>
                                                                <td>
                                                                    @if($support->status === 0)
                                                                        <span class="badge bg-info text-dark">
                                                                            Running
                                                                        </span>
                                                                    @else
                                                                        <span class="badge bg-success ">
                                                                            Closed
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%">
                                                                    <b>Message</b>
                                                                </td>
                                                                <td>{{ $support->message }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="width: 30%"><b>Email</b></td>
                                                                <td>{{ $support->email }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
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
