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
                    <h5 class="card-title mb-0"> {{ $pageTitle }} </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="#">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Gateway *</label>
                                    <select class="form-control" name="smsGetway" id="smsGetway">
                                        <option selected="" disabled="">Select SMS gateway...</option>
                                        <option value="twilio">Twilio</option>
                                        <option value="clickatell">Clickatell</option>
                                    </select>
                                </div>

                                <div class="form-group twilio mt-2" style="display: none;">
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
                                </div>
                                <div class="form-group mt-2">
                                    <input type="submit" value="Submit" class="btn btn-primary">
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
    <script>
        $('document').ready(function () {
            $(document).on('change', '#smsGetway', function () {
                var smsGetway = $('#smsGetway').val();

                if(smsGetway == 'twilio') {
                 $('.twilio').css('display', 'block');
                 $('.clickatell').css('display', 'none');
                }else {
                    $('.twilio').css('display', 'none');
                    $('.clickatell').css('display', 'block');
                }

            })

        })
    </script>
@endsection
