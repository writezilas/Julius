<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('user.dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('assets/images/autobidder_dark.png') }}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('user.dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('assets/images/autobidder_light.png') }}" alt="" height="17">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>@lang('translation.menu')</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('user.dashboard') }}">
                        <span>@lang('translation.dashboard')</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('users.sold_shares') }}">
                        <span>@lang('translation.soldshares')</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('users.bought_shares') }}">
                        <span>@lang('translation.boughtshares')</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('users.referrals') }}">
                        <span>@lang('translation.refferals')</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('page.how_it_work') }}">
                        <span>@lang('translation.how_it_works')</span>
                    </a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link menu-link" href="my-wallet">
                        <span>@lang('translation.my_wallet')</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="donate-to-us">
                        <span>@lang('translation.donate_to_us')</span>
                    </a>
                </li>  --}}
                @if(auth()->user()->role_id == 2 && \App\Models\ChatSetting::isChatEnabled())
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('chat.index') }}">
                        <i class="ri-chat-3-line"></i>
                        <span>Chat</span>
                        <span class="badge bg-danger chat-unread-count" id="sidebarChatBadge" style="display: none;">0</span>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('users.support') }}">
                        <span>@lang('translation.support')</span>
                    </a>
                </li>

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
