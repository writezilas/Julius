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
                    <table id="alternative-pagination" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>SR No.</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Status</th>
                                {{-- <th>Action</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->email}}</td>
                                    <td>{{$user->role->name}}</td>
                                    <td>{{\Carbon\Carbon::parse($user->created_at)}}</td>
                                    <td>
                                        <select class="form-control form-control-sm"
                                            id="status-update"
                                            @disabled($user->role_id == 1)
                                            data-id={{$user->id}}>
                                            <option value="pending" @selected($user->status == 'pending')>Pending</option>
                                            <option value="block" @selected($user->status == 'block')>Block</option>
                                            <option value="suspend" @selected($user->status == 'suspend')>Suspend</option>
                                            <option value="fine" @selected($user->status == 'fine') >Fine</option>
                                        </select>
                                    </td>
                                    {{-- <td><button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-5-line"></i> </button></td> --}}
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
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

@endsection
@section('script')

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
