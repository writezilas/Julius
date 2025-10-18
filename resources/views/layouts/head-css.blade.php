<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Critical Resource Hints & Preloads for Performance -->
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//cdn.datatables.net">
<link rel="dns-prefetch" href="//cdn.jsdelivr.net">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Font Preloads for Performance -->
<link rel="preload" href="{{ URL::asset('assets/fonts/materialdesignicons-webfont.woff2') }}" as="font" type="font/woff2" crossorigin>

<!-- Critical CSS Preloads (First 4 Most Important Styles) -->
<link rel="preload" href="{{ URL::asset('assets/css/bootstrap.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<link rel="preload" href="{{ URL::asset('assets/css/icons.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<link rel="preload" href="{{ URL::asset('assets/css/app.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<link rel="preload" href="{{ URL::asset('assets/css/custom.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">

<!-- Critical CSS Inline for Above-the-fold Content -->
<style>
/* Critical inline CSS for immediate rendering */
body{font-family:"Poppins",sans-serif;background:#f8fafc;margin:0;padding:0;}
.navbar{background:#fff;border-bottom:1px solid #e5e7eb;}
.sidebar{background:#1e293b;color:#fff;}
.main-content{margin-left:250px;padding:20px;}
@media (max-width:768px){.main-content{margin-left:0;}}
.btn-primary{background:#667eea;border-color:#667eea;}
.card{background:#fff;border:none;border-radius:12px;box-shadow:0 0 35px rgba(154,161,171,0.15);}
</style>

@yield('css')

<!-- CSS Loading Optimization Script -->
<script>
/*! Inline CSS Loading Polyfill for Critical Performance */
!function(e){"use strict";var t=function(t,n,r){var o,i=e.document,c=i.createElement("link");if(n)o=n;else{var a=(i.body||i.getElementsByTagName("head")[0]).childNodes;o=a[a.length-1]}var u=i.styleSheets;c.rel="stylesheet",c.href=t,c.media="only x";function s(e){if(i.body)return e();setTimeout(function(){s(e)})}function l(){for(var e,n=0;n<u.length;n++)u[n].href&&u[n].href.indexOf(t)>-1&&(e=!0);e?c.media=r||"all":setTimeout(l)}s(function(){o.parentNode.insertBefore(c,n?o:o.nextSibling)}),c.addEventListener&&c.addEventListener("load",l),c.onloadcssdefined=l,l()};"undefined"!=typeof module?module.exports=t:e.loadCSS=t}("undefined"!=typeof global?global:this);
</script>

<!-- Layout config Js -->
<script src="{{ URL::asset('assets/js/layout.js') }}"></script>
<!-- CSS Loader Helper -->
<script src="{{ URL::asset('assets/js/css-loader.js') }}"></script>

<!-- Primary CSS Files (Critical Path) -->
<noscript><link href="{{ URL::asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"></noscript>
<noscript><link href="{{ URL::asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css"></noscript>
<noscript><link href="{{ URL::asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css"></noscript>
<noscript><link href="{{ URL::asset('assets/css/custom.min.css') }}" rel="stylesheet" type="text/css"></noscript>

<!-- Secondary CSS Files (Non-Critical - Load Asynchronously) -->
<link rel="preload" href="{{ URL::asset('assets/css/admin-panel-custom.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="{{ URL::asset('assets/css/admin-panel-custom.css') }}" rel="stylesheet" type="text/css"></noscript>

<link rel="preload" href="{{ URL::asset('assets/css/logo.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="{{ URL::asset('assets/css/logo.css') }}" rel="stylesheet" type="text/css"></noscript>

<link rel="preload" href="{{ URL::asset('assets/css/notification-fix.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="{{ URL::asset('assets/css/notification-fix.css') }}" rel="stylesheet" type="text/css"></noscript>

<link rel="preload" href="{{ URL::asset('assets/css/new-payment-forms.css') }}?v={{ time() }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="{{ URL::asset('assets/css/new-payment-forms.css') }}?v={{ time() }}" rel="stylesheet" type="text/css"></noscript>
<!-- Google Fonts (Preconnected and Optimized) -->
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>

<!-- External Libraries (Preloaded for Performance) -->
{{--data table --}}
<link rel="preload" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css"></noscript>

{{--sweetalert --}}
<link rel="preload" href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css')}}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css"></noscript>

{{--select2--}}
<link rel="preload" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"></noscript>

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
