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
            <div class="card-header">
                <h5 class="card-title mb-0">{{$pageTitle}}</h5>
            </div>
            <div class="card-body">
                <table id="alternative-pagination" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>SR No.</th>
                            <th>Share type</th>
                            <th>Username</th>
                            <th>Share quantity</th>
                            <th>Allocate At</th>
                            @can('allocate-share-to-user-history-delete')
                            <th>Action</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allocateShares as $share)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{ $share->userShare->trade->name }}</td>
                            <td>{{ $share->userShare->user->username }}</td>
                            <td>{{ $share->shares }}</td>
                            <td>{{ \Carbon\Carbon::parse($share->created_at) }}</td>
                            <td>
                                @can('allocate-share-to-user-history-delete')
                                <a href="{{ route('admin.allocate.share.destroy', $share->user_share_id) }}" onclick="return confirm('Are you sure want to remove the allocate share date? you can not restore it.')" class="btn btn-sm btn-danger">
                                    Delete
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr class="odd">
                            <td valign="top" colspan="7" class="dataTables_empty">
                                No data found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!--end col-->
</div>

@endsection
