<!doctype html >

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="{{ auth()->check() && auth()->user()->role_id != 2 ? 'vertical' : 'horizontal'}}" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-layout-mode="{{auth()->check() && auth()->user()->mode ? auth()->user()->mode : 'light' }}">

<head>
    <meta charset="utf-8" />
    <title>@yield('title')| {{env('APP_NAME', 'AUTO BIDDER')}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Autobidder.live is peer-to-peer investment platform that simulates the stock exchange market." name="description" />
    <meta content="Autobidder" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('assets/images/favicon.ico')}}">
    @include('layouts.head-css')

</head>

@section('body')
    @include('layouts.body')
@show
    <!-- Begin page -->
    <div id="layout-wrapper">
        @if(request()->get('md'))
            @include('layouts.sidebar2')
        @else
            @if(auth()->check() && auth()->user()->role_id == 2)
                @include('user-panel.partials.topbar')
                @include('user-panel.partials.sidebar')
            @else
                @include('layouts.topbar')
                @include('layouts.sidebar')
            @endif
        @endif
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    {{-- @include('layouts.customizer') --}}

    <!-- JAVASCRIPT -->
    @include('layouts.vendor-scripts')
    
    @auth
    <!-- User Status Monitor -->
    <script>
        window.Laravel = {
            user: {
                id: {{ auth()->user()->id }},
                username: '{{ auth()->user()->username }}',
                status: '{{ auth()->user()->status }}'
            }
        };
    </script>
    <script src="{{ asset('assets/js/suspension-monitor.js') }}"></script>
    <script src="{{ asset('assets/js/user-status-monitor.js') }}"></script>
    
    @if(auth()->user()->role_id === 2)
    <!-- Global chat unread count functionality -->
    <script>
    class GlobalChatNotification {
        constructor() {
            this.init();
        }

        init() {
            this.updateUnreadCount();
            // Update every 30 seconds
            setInterval(() => {
                this.updateUnreadCount();
            }, 30000);
        }

        async updateUnreadCount() {
            try {
                const response = await fetch('/chat/unread-count');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        const count = data.unread_count;
                        const badge = document.getElementById('sidebarChatBadge');
                        
                        if (badge) {
                            if (count > 0) {
                                badge.textContent = count;
                                badge.style.display = 'inline-block';
                            } else {
                                badge.style.display = 'none';
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error fetching unread count:', error);
            }
        }
    }

    // Initialize global chat notifications
    document.addEventListener('DOMContentLoaded', function() {
        window.globalChatNotification = new GlobalChatNotification();
    });
    </script>
    @endif
    @endauth

</body>
<script>
  
</script>
</html>
