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
                    <table class="table table-bordered">
                        <tr>
                            <th>Name</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td>{{ $user->username }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $user->phone }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>{{ $user->status === 'pending' ? 'Active' : $user->status }}</td>
                        </tr>
                        <tr>
                            <th>Mpesa name</th>
                            <td>{{ json_decode($user->business_profile)->mpesa_name }}</td>
                        </tr>
                        <tr>
                            <th>Mpesa no</th>
                            <td>{{ json_decode($user->business_profile)->mpesa_no }}</td>
                        </tr>
                        <tr>
                            <th>Mpesa till no</th>
                            <td>{{ json_decode($user->business_profile)->mpesa_till_no }}</td>
                        </tr>
                        <tr>
                            <th>Mpesa till name</th>
                            <td>{{ json_decode($user->business_profile)->mpesa_till_name }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">User status update</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <form action="{{ route('user.status.update', $user->id) }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="form-group">
                                    <label>User status</label>
                                    <select class="form-control" id="userstatus" name="status">
                                        <option value="pending" @selected($user->status == 'pending')>Active</option>
                                        <option value="suspend" @selected($user->status == 'suspend')>Suspend</option>
                                        <option value="block" @selected($user->status == 'block')>Block</option>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-6 suspend" style="display:none;">
                                    <div class="form-group">
                                        <label>Block time (hours)</label>
                                        <input type="number" class="form-control" name="time" value="1">
                                        @if($user->block_until)
                                            <span class="text-warning italic">
                                                Block until: {{ \Carbon\Carbon::parse($user->block_until)->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @can('customer-update')
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                                @endcan
                            </div>
                        </form>
                    </table>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

@endsection
@section('script')
<script>
    $('#userstatus').change(function(){
        $('.suspend').hide();
        if($(this).val() === 'suspend'){
            $('.suspend').show();
        }
    })
    if($('#userstatus').val() == 'suspend'){
        $('.suspend').show();
    }
</script>
@endsection
