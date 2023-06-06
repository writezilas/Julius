@extends('layouts.master')
@php($pageTitle = __('translation.soldshares') . ' Info')
@section('title') {{$pageTitle}}  @endsection
@section('css')
@endsection
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
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Expected Return</th>
                                <th>Expense</th>
                                <th>Status</th>
                                <th>Authentication</th>
                                <th>Time remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i=1; $i <= 200; $i++)
                                <tr>
                                    <td>{{$i}}</td>
                                    <td>2022-01-10</td>
                                    <td>2000</td>
                                    <td>2022-01-10</td>
                                    <td>20</td>
                                    <td><button class="btn btn-sm btn-soft-info">Paring</button></td>
                                    <td>Yes</td>
                                    <td>1 hour 20 min remaining</td>
                                </tr>
                            @endfor
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
