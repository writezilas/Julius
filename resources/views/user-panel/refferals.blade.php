@extends('layouts.master')
@php($pageTitle = 'Refferal Code')
@section('title') {{$pageTitle}}  @endsection
@section('content')
	@component('components.breadcrumb')
		@slot('li_1') Pages @endslot
		@slot('title')  {{$pageTitle}} @endslot
	@endcomponent
	<div class="row justify-content-center mt-4">
        <div class="col-lg-5">
            <div class="text-center mb-4">
                <h4 class="fw-semibold fs-22">Your referral link</h4>
                <p class="text-muted mb-2 fs-15">Copy Below link</p>
                <p class="text-muted mb-2 fs-15 mt-3">
                    <a href="javascript:;" id="referralCode">{{url('/register?refferal_code='.auth()->user()->username)}}</a>
                </p>
                <button type="button" class="btn btn-link" onclick="handleCopyLink()">Copy link</button>
            </div>
        </div><!--end col-->
    </div><!--end row-->
    @if(count($refferals))
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="alternative-pagination" class="table  align-middle table-hover table-bordered" style="width:100%">
                            <thead>
                            <tr>
                                <th>SR No.</th>
                                <th>Username</th>
                                <th>Signup Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($refferals as $ref)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{ $ref->username }}</td>
                                    <td>{{ \Carbon\Carbon::parse($ref->created_at)->format('d M Y') }}</td>
                                    <td>Ksh {{ $ref->ref_amount }}</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $ref->ref_amount ? 'Paid' : 'Unpaid'  }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>
    @endif
@endsection

@section('script')
    <script>
        function handleCopyLink() {
            let text = document.getElementById("referralCode");
            // Get the text to copy

            // Create a range object
            var range = document.createRange();

            // Select the text to copy
            range.selectNode(text);

            // Add the range to the selection
            window.getSelection().addRange(range);

            try {
                // Execute the copy command
                document.execCommand('copy');
                alert('Text copied to clipboard');
            } catch (err) {
                console.error('Unable to copy text', err);
            }

            // Clear the selection
            window.getSelection().removeAllRanges();
        }
    </script>
@endsection

