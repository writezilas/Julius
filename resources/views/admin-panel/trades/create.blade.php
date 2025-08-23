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
                    <a href="{{ route('admin.trade.index') }}" class="btn btn-primary">
                        all trades
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.trade.store') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
{{--                            <div class="col-lg-6">--}}
{{--                                <div class="mb-3">--}}
{{--                                    <label class="form-label">quantity</label>--}}
{{--                                    <input type="number" class="form-control" name="quantity" value="{{ old('quantity') }}" autocomplete="none">--}}
{{--                                    @error('quantity')--}}
{{--                                    <span class="invalid-feedback" role="alert">--}}
{{--                                        <strong>{{ $message }}</strong>--}}
{{--                                    </span>--}}
{{--                                    @enderror--}}
{{--                                </div>--}}
{{--                            </div>--}}
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" class="form-control" name="price" value="{{ old('price') }}">
                                    @error('price')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
{{--                            <div class="col-lg-6">--}}
{{--                                <div class="mb-3">--}}
{{--                                    <label class="form-label">Buying price</label>--}}
{{--                                    <input type="number" class="form-control" name="buying_price" value="{{ old('buying_price') }}">--}}
{{--                                    @error('buying_price')--}}
{{--                                    <span class="invalid-feedback" role="alert">--}}
{{--                                        <strong>{{ $message }}</strong>--}}
{{--                                    </span>--}}
{{--                                    @enderror--}}
{{--                                </div>--}}
{{--                            </div>--}}

                            <div class="col-lg-12">
                                <div class="form-group float-end">
                                    <button type="submit" class="btn btn-primary">Create trade</button>
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

@endsection
