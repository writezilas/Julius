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
                <h5 class="card-title mb-0"> {{ $pageTitle }} </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{route('admin.setting.sms.store')}}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gateway *</label>
                                <select class="form-control" name="smsGetway" id="smsGetway">
                                    <option selected="" disabled="">Select SMS gateway...</option>
                                    <option value="zettatel" selected>Zet Tatel</option>
                                    {{-- <option value="twilio">Twilio</option>
                                    <option value="clickatell">Clickatell</option> --}}
                                </select>
                            </div>

                            {{-- <div class="form-group twilio mt-2" style="display: none;">
                                <label>ACCOUNT SID *</label>
                                <input type="text" name="account_sid" class="form-control twilio-option" value="">
                            </div>
                            <div class="form-group twilio mt-2" style="display: none;">
                                <label>AUTH TOKEN *</label>
                                <input type="text" name="auth_token" class="form-control twilio-option" value="">
                            </div>
                            <div class="form-group twilio mt-2" style="display: none;">
                                <label>Twilio Number *</label>
                                <input type="text" name="twilio_number" class="form-control twilio-option" value="">
                            </div>
                            <div class="form-group clickatell mt-2">
                                <label>API Key *</label>
                                <input type="text" name="api_key" class="form-control clickatell-option" value="">
                            </div> --}}
                            
                            <div class="form-group zettatel mt-2">
                                <label>User Id *</label>
                                <input type="text" name="zettatel_user_id" class="form-control zettatel-option"  placeholder="User id" value="{{isset($smsSetting) ? $smsSetting['zettatel_user_id'] : '' }}">
                                @error('zettatel_user_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group zettatel mt-2">
                                <label>Password *</label>
                                <input type="password" name="zettatel_password" class="form-control zettatel-option" placeholder="****">
                                @error('zettatel_password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group zettatel mt-2">
                                <label>Sender Id *</label>
                                <input type="text" name="zettatel_senderid" class="form-control zettatel-option" placeholder="Sender id" value="{{isset($smsSetting) ? $smsSetting['zettatel_senderid'] : '' }}">
                                @error('zettatel_senderid')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group zettatel mt-2">
                                <label>Msg Type *</label>
                                <input type="text" name="zettatel_msg_type" class="form-control zettatel-option" placeholder="text" value="{{isset($smsSetting) ? $smsSetting['zettatel_msg_type'] : '' }}">
                                @error('zettatel_msg_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group zettatel mt-2">
                                <label>Duplicate Check *</label>
                                <select class="form-control zettatel-option"  name="zettatel_duplicatecheck">
                                    <option value="true">True</option>
                                    <option value="false">False</option>
                                </select>
                                @error('zettatel_duplicatecheck')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group zettatel mt-2">
                                <label>Send Method *</label>
                                <select class="form-control zettatel-option"  name="zettatel_sendmethod">
                                    <option value="quick">Quick</option>
                                </select>
                                @error('zettatel_sendmethod')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @can('sms-api-page-update')
                            <div class="form-group mt-2">
                                <input type="submit" value="Submit" class="btn btn-primary">
                            </div>
                            @endcan
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
<script>
    $('document').ready(function() {
        $(document).on('change', '#smsGetway', function() {
            var smsGetway = $('#smsGetway').val();
        
            $('.'+smsGetway).css('display', 'block');
    
        })

    })

</script>
@endsection
