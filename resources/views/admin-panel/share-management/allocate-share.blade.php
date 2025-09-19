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
                    <form action="{{ route('admin.allocate.saveAllocateShare') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Wallet</label>
                                    <select name="trade_id" class="form-control" id="allocateShareTradeId">
                                        <option value="" disabled selected>Select a wallet</option>
                                        @foreach($trades as $trade)
                                            <option value="{{ $trade->id }}">
                                                {{ $trade->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('trade_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-4"></div>
                            <div class="col-lg-4"></div>

                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">To user</label>
                                    <select name="to_user" class="form-control" id="allocateShareFromUser">
                                        <option value="" disabled selected>Select a user to allocate share</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @selected(old('to_user') == $user->id)>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('to_user')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">No of Shares to transfer</label>
                                    <input type="text" class="form-control" name="no_of_share_for_transfer" value="{{ old('no_of_share_for_transfer') }}" autocomplete="none">
                                    @error('no_of_share_for_transfer')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Time period</label>
                                    <select name="period" class="form-control" id="allocateShareFromUser">
                                        @if($periods->count() > 0)
                                            @foreach($periods as $period)
                                                <option value="{{ $period->days }}" @selected(old('period') == $period->days)>
                                                    @if(isset($period->label))
                                                        {{ $period->label }}
                                                    @else
                                                        {{ $period->days }} days ({{ $period->percentage }}% return)
                                                    @endif
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>No trade periods available</option>
                                        @endif
                                    </select>
                                    @error('period')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group float-end">
                                    <button type="submit" class="btn btn-primary">Allocate share</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>
@endsection
@section('script')
{{--    <script>--}}
{{--        $(document).on('change', '#allocateShareTradeId', function () {--}}
{{--            const allocateShareFromUser = $('#allocateShareFromUser').val();--}}
{{--            const allocateShareTradeId = $('#allocateShareTradeId').val();--}}

{{--            if(allocateShareFromUser && allocateShareTradeId) {--}}
{{--                $.post('{{ route('admin.getShareByTradeAndUser') }}',--}}
{{--                    {_token:'{{ csrf_token() }}', userId: allocateShareFromUser, tradeId: allocateShareTradeId}, function (data) {--}}
{{--                    $('#available_share_per_user').val(data);--}}
{{--                });--}}
{{--            }--}}
{{--        });--}}
{{--    </script>--}}
@endsection
