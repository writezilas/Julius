@extends('layouts.master')
@php($pageTitle = __('translation.soldshares') . ' Info')
@section('title') {{$pageTitle}}  @endsection
@section('css')
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script src="{{ URL::asset('assets/js/pages/datatables.init.js') }}"></script>
    <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script>
@endsection
