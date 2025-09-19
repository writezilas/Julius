<meta name="csrf-token" content="{{ csrf_token() }}">
@yield('css')
<!-- Layout config Js -->
<script src="{{ URL::asset('assets/js/layout.js') }}"></script>
<!-- Bootstrap Css -->
<link href="{{ URL::asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ URL::asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ URL::asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
<!-- custom Css-->
<link href="{{ URL::asset('assets/css/custom.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
<!-- Enhanced Admin Panel Css-->
<link href="{{ URL::asset('assets/css/admin-panel-custom.css') }}" rel="stylesheet" type="text/css" />
<!-- Logo Css-->
<link href="{{ URL::asset('assets/css/logo.css') }}" rel="stylesheet" type="text/css" />
<!-- Notification Badge Fix Css-->
<link href="{{ URL::asset('assets/css/notification-fix.css') }}" rel="stylesheet" type="text/css" />
{{--data table --}}
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
{{--sweetalert --}}
<link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css" />
{{--select2--}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

{{-- @yield('css') --}}


<style rel="stylesheet" type="text/css">
    .invalid-feedback {
        display: block !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #414487 !important;
        border: 1px solid #414487 !important;
    }
</style>
