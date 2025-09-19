@extends('layouts.master')
@section('title') {{$pageTitle}}  @endsection
@section('content')

    @component('components.breadcrumb')
        @slot('li_1') @lang('translation.dashboard') @endslot
        @slot('title') {{$pageTitle}} @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0"> {{ $pageTitle }} </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.saveTaxRate') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label>Tax rate <smal>%</smal> *</label>
                            <input type="number" name="tax_rate" class="form-control" value="{{ $taxRate }}">
                            @error('tax_rate')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>
                        <div class="mt-3">
                            <input type="submit" value="Submit" class="btn btn-primary float-end">
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

    </script>
@endsection
