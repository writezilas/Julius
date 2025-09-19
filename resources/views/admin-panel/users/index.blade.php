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
                <div class="card-header">
                    <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                </div>
                <div class="card-body">
                    <table id="scroll-horizontal" class="table table-bordered dt-responsive nowrap align-middle mdl-data-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>SR No.</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                                @can('customer-view')
                                <th>Action</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->username}}</td>
                                    <td>{{$user->email}}</td>
                                    <td>{{$user->role ? $user->role->name : 'No Role Assigned'}}</td>
                                    <td>{{\Carbon\Carbon::parse($user->created_at)}}</td>
                                    @can('customer-view')
                                    <td>
                                        <a href="{{ route('user.single', $user->id) }}" class="btn btn-primary">View</a>
                                    </td>
                                    @endcan
                                    
                                </tr>
                            @empty
                                <tr class="odd">
                                    <td valign="top" colspan="7" class="dataTables_empty">
                                        {{$emptyMessage}}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{-- <div class="pagination float-end">
                        {{$users->links()}}
                    </div> --}}
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

@endsection
@section('script')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ URL::asset('assets/js/pages/datatables.init.js') }}"></script>
    <script type="text/javascript">
        $(document).on('change', '#status-update', function(e) {
            e.preventDefault();
            $this = $(this);
            $id = $(this).attr('data-id');
            Swal.fire({
                title: "Are you sure?",
                text: "You want to update the status",
                icon: "warning",
                showCancelButton: true,
                confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                cancelButtonClass: 'btn btn-danger w-xs mt-2',
                confirmButtonText: "Yes, delete it!",
                buttonsStyling: false,
                showCloseButton: true
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        type: "POST",
                        url: "{{url('users/_id/status-update')}}".replace('_id', $id),
                        data:{
                            status:$this.val(),
                            _token: "{{csrf_token()}}",
                        },
                        success: function(){
                            Swal.fire({
                                title: 'Updated!',
                                text: 'Your status has been updated.',
                                icon: 'success',
                                confirmButtonClass: 'btn btn-primary w-xs mt-2',
                                buttonsStyling: false
                            }).then(function (result) {
                                window.location.reload();
                            });
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            console.log("Status: " + textStatus);
                            console.log("Error: " + errorThrown);
                        }
                    });
                }
            });

        })
    </script>
@endsection
