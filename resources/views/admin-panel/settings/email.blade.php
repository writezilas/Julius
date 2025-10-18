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
                {{-- Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-all me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Error Messages --}}
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-block-helper me-2"></i>
                        <strong>Please correct the following errors:</strong>
                        <ul class="mt-2 mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{route('admin.setting.email.store')}}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Server Address *</label>
                                <input type="text" name="mail_host" class="form-control @error('mail_host') is-invalid @enderror" 
                                       value="{{old('mail_host', $emails['MAIL_HOST'])}}" required="" placeholder="e.g., imap.titan.email">
                                @error('mail_host')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mt-2">
                                <label>IMAP Port *</label>
                                <input type="number" name="port" class="form-control @error('port') is-invalid @enderror" 
                                       value="{{old('port', $emails['MAIL_PORT'])}}" required="" placeholder="e.g., 993" min="1" max="65535">
                                @error('port')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mt-2">
                                <label>Encryption *</label>
                                <select name="encryption" class="form-control @error('encryption') is-invalid @enderror" required="">
                                    <option value="">Select Encryption</option>
                                    <option value="ssl" {{ old('encryption', $emails['MAIL_ENCRYPTION']) == 'ssl' ? 'selected' : '' }}>SSL/TLS</option>
                                    <option value="tls" {{ old('encryption', $emails['MAIL_ENCRYPTION']) == 'tls' ? 'selected' : '' }}>STARTTLS</option>
                                </select>
                                @error('encryption')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Username *</label>
                                <input type="email" name="mail_username" class="form-control @error('mail_username') is-invalid @enderror" 
                                       value="{{old('mail_username', $emails['MAIL_USERNAME'])}}" required="" placeholder="e.g., info@autobidder.com">
                                @error('mail_username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mt-2">
                                <label>Password *</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="{{ !empty($emails['MAIL_PASSWORD']) ? '••••••••' : 'Enter password' }}" 
                                       {{ !empty($emails['MAIL_PASSWORD']) ? '' : 'required' }}>
                                @if(!empty($emails['MAIL_PASSWORD']))
                                    <small class="form-text text-muted">Leave blank to keep current password</small>
                                @endif
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mt-2">
                                <label>Mail From *</label>
                                <input type="text" name="mail_from_name" class="form-control @error('mail_from_name') is-invalid @enderror" 
                                       value="{{old('mail_from_name', $emails['MAIL_FROM_NAME'] ?? 'Autobidder')}}" required="" placeholder="e.g., Autobidder">
                                @error('mail_from_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @can('email-api-page-update')
                        <div class="form-group mt-2">
                            <input type="submit" value="Submit" class="btn btn-primary">
                        </div>
                        @endcan
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
