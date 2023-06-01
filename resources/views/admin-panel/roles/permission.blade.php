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
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">Module name</th>
                            <th scope="col">Permissions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Settings</td>
                            <td>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="trading-period">
                                    <label class="form-check-label" for="trading-period">Set trading period</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="sms-api">
                                    <label class="form-check-label" for="sms-api">Sms api setting</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="email-api">
                                    <label class="form-check-label" for="email-api">Email api setting</label>
                                </div>
                            </td>
                            </tr>
                            <tr>
                                <td>Communications</td>
                                <td>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="trading-period">
                                        <label class="form-check-label" for="trading-period">Email</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" checked role="switch" id="sms-api">
                                        <label class="form-check-label" for="sms-api">Announcement</label>
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>


@endsection
