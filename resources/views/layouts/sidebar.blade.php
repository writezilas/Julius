<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('admin.index') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('assets/images/autobidder_dark.png') }}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('admin.index') }}" class="logo logo-light">
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
                <a class="nav-link menu-link" href="{{ route('admin.index') }}">
                   <i class="ri-home-2-fill"></i>  <span>@lang('translation.dashboard')</span>
                </a>

                @canAny(['staff-index','role-index','customer-index'])
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarUsers" data-bs-toggle="collapse" role="button" aria-expanded="{{ areActiveRoutesBool(['users.status']) }}" aria-controls="sidebarCustomer">
                        <i class="ri-user-2-fill"></i> <span>@lang('User Management')</span>
                    </a>
                    
                    <div class="collapse menu-dropdown {{ areActiveRoutesBool(['users.status', 'admin.role.index', 'admin.role.permission', 'admin.staff.index', 'admin.staff.create', 'admin.staff.edit']) ? 'show' : '' }}" id="sidebarUsers">
                        <ul class="nav nav-sm flex-column">
                            @can('customer-index')
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="#sidebarCustomer" data-bs-toggle="collapse" role="button" aria-expanded="{{ areActiveRoutesBool(['users.status']) }}" aria-controls="sidebarUsers">
                                    <span>@lang('Customer Management')</span>
                                </a>
                                <div class="collapse menu-dropdown {{ areActiveRoutesBool(['users.status']) ? 'show' : '' }}" id="sidebarCustomer">
                                    <ul class="nav nav-sm flex-column">

                                        <li class="nav-item">
                                            <a href="{{route('users.status', 'block')}}" class="nav-link {{ Request::is('users/block') ? 'active' : '' }}">@lang('Block Users')</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{route('users.status', 'suspend')}}" class="nav-link {{ Request::is('users/suspend') ? 'active' : '' }}">@lang('Suspend Users')</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{route('users.status', 'fine')}}" class="nav-link {{ Request::is('users/fine') ? 'active' : '' }}">@lang('Active Users')</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{route('users.status', 'all')}}" class="nav-link {{ Request::is('users/all') ? 'active' : '' }}">@lang('All Users')</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endcan
                            @can('role-index')
                            <li class="nav-item">
                                <a href="{{ route('admin.role.index') }}" class="nav-link {{ areActiveRoutes(['admin.role.index', 'admin.role.permission']) }}">@lang('Role permissions')</a>
                            </li>
                            @endcan
                            @can('staff-index')
                            <li class="nav-item">
                                <a href="{{ route('admin.staff.index') }}" class="nav-link {{ areActiveRoutes(['admin.staff.index', 'admin.staff.create', 'admin.staff.edit']) }}">@lang('Staffs')</a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                    
                </li>
                @endcanAny
                @canAny(['allocate-share-to-user', 'transfer-share-from-user', 'allocate-share-history'])
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarShare"
                       data-bs-toggle="collapse"
                       role="button"
                       aria-expanded="{{ areActiveRoutesBool(['admin.allocate.share', 'admin.transfer.share', 'admin.allocate.share.history']) }}"
                       aria-controls="sidebarShare">
                        <i class="ri-share-box-fill"></i> <span>@lang('Share Managament')</span>
                    </a>
                    <div class="collapse menu-dropdown {{ areActiveRoutesBool(['admin.allocate.share', 'admin.transfer.share', 'admin.allocate.share.history']) ? 'show' : '' }}" id="sidebarShare">
                        <ul class="nav nav-sm flex-column">
                            @can('allocate-share-to-user')
                            <li class="nav-item">
                                <a href="{{ route('admin.allocate.share') }}" class="nav-link {{ areActiveRoutes(['admin.allocate.share']) }}">@lang('Allocate share to user')</a>
                            </li>
                            @endcan
                            @can('allocate-share-to-user-history')
                            <li class="nav-item">
                                <a href="{{ route('admin.allocate.share.history') }}" class="nav-link {{ areActiveRoutes(['admin.allocate.share.history']) }}">@lang('Allocate share history')</a>
                            </li>
                            @endcan
                            @can('transfer-share-from-user')
                            <li class="nav-item">
                                <a href="{{ route('admin.transfer.share') }}" class="nav-link {{ areActiveRoutes(['admin.transfer.share']) }}">@lang('Transfer share from user')</a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcanAny
                @canAny(['announcement-index', 'send-email', 'send-sms', 'support-index'])
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarCommunication"
                       data-bs-toggle="collapse" role="button"
                       aria-expanded="{{ areActiveRoutesBool(['announcement.create', 'email.create', 'sms.create', 'admin.support']) }}"
                       aria-controls="sidebarCommunication"
                    >
                        <i class="ri-message-2-fill"></i> <span>@lang('Communications')</span>
                    </a>
                    <div class="collapse menu-dropdown {{ areActiveRoutesBool(['announcement.index', 'announcement.create', 'announcement.edit', 'email.create', 'sms.create', 'admin.support']) ? 'show' : '' }}" id="sidebarCommunication">
                        <ul class="nav nav-sm flex-column">
                        @can('announcement-index')
                            <li class="nav-item">
                                <a href="{{ route('announcement.index') }}" class="nav-link {{ areActiveRoutes(['announcement.create', 'announcement.index', 'announcement.edit']) }}">@lang('Announcements')</a>
                            </li>
                            @endcan
                            @can('send-email')
                            <li class="nav-item">
                                <a href="{{ route('email.create') }}" class="nav-link {{ areActiveRoutes(['email.create']) }}">@lang('Emails')</a>
                            </li>
                            @endcan
                            @can('send-sms')
                            <li class="nav-item">
                                <a href="{{ route('sms.create') }}" class="nav-link {{ areActiveRoutes(['sms.create']) }}">@lang('Sms')</a>
                            </li>
                            @endcan
                            @can('support-index')
                            <li class="nav-item">
                                <a href="{{ route('admin.support') }}" class="nav-link {{ areActiveRoutes(['admin.support']) }}">@lang('Supports')</a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcanAny
                @canAny(['trade-index', 'trade-periods-index'])
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarTrades"
                       data-bs-toggle="collapse" role="button"
                       aria-expanded="{{ areActiveRoutesBool(['admin.trade.index', 'admin.trade.create', 'admin.trade.edit', 'admin.period.index', 'admin.period.edit', 'admin.period.create']) }}"
                       aria-controls="sidebarCommunication"
                    >
                        <i class="ri-money-dollar-box-fill"></i> <span>@lang('Trades settings')</span>
                    </a>
                    <div class="collapse menu-dropdown {{ areActiveRoutesBool(['admin.trade.index', 'admin.trade.create', 'admin.trade.edit', 'admin.period.index', 'admin.period.edit', 'admin.period.create']) ? 'show' : '' }}" id="sidebarTrades">
                        <ul class="nav nav-sm flex-column">
                            @can('trade-index')
                            <li class="nav-item">
                                <a href="{{ route('admin.trade.index') }}" class="nav-link {{ areActiveRoutes(['admin.trade.index', 'admin.trade.create', 'admin.trade.edit']) }}">@lang('Trades')</a>
                            </li>
                            @endcan
                            @can('trade-periods-index')
                            <li class="nav-item">
                                <a href="{{ route('admin.period.index') }}" class="nav-link {{ areActiveRoutes(['admin.period.index', 'admin.period.edit', 'admin.period.create']) }}">@lang('Trading periods')</a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcanAny

                @canAny(['how-it-work-page-view', 'term-and-condition-page-view', 'privacy-policy-page-view', 'confidentiality-policy-page-view'])
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarFrontEndSettings" data-bs-toggle="collapse" role="button" aria-expanded="{{ areActiveRoutesBool(['policy.edit']) }}" aria-controls="sidebarShare">
                        <i class="ri-list-settings-fill"></i> <span>@lang('Frontend settings')</span>
                    </a>
                    <div class="collapse menu-dropdown {{ areActiveRoutesBool(['policy.edit']) ? 'show' : '' }}" id="sidebarFrontEndSettings">
                        <ul class="nav nav-sm flex-column">
                            @can('how-it-work-page-view')
                            <li class="nav-item">
                                <a href="{{ route('policy.edit', 'how-it-work') }}" class="nav-link {{ Request::is('admin/policy/how-it-work') ? 'active' : '' }}">@lang('How it works page')</a>
                            </li>
                            @endcan
                            @can('term-and-condition-page-view')
                            <li class="nav-item">
                                <a href="{{ route('policy.edit', 'terms-and-conditions') }}" class="nav-link {{ Request::is('admin/policy/terms-and-conditions') ? 'active' : '' }}">@lang('Terms and conditions')</a>
                            </li>
                            @endcan
                            @can('privacy-policy-page-view')
                            <li class="nav-item">
                                <a href="{{ route('policy.edit', 'privacy-policy') }}" class="nav-link {{ Request::is('admin/policy/privacy-policy') ? 'active' : '' }}">@lang('Privacy policy')</a>
                            </li>
                            @endcan
                            @can('confidentiality-policy-page-view')
                            <li class="nav-item">
                                <a href="{{ route('policy.edit', 'confidentiality-policy') }}" class="nav-link {{ Request::is('admin/policy/confidentiality-policy') ? 'active' : '' }}">@lang('Confidentiality policy')</a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcanAny
                @canAny(['general-setting-view', 'set-min-max-trading-amount-view', 'set-income-tax-rate-view', 'sms-api-page-view', 'email-api-page-view', 'payments-api-page-view'])
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarSettings" data-bs-toggle="collapse" role="button" aria-expanded="{{ areActiveRoutesBool(['admin.updateTradingPrice', 'admin.setTaxRate', 'admin.setting.mail.create', 'admin.setting.sms.create', 'admin.general-setting']) }}" aria-controls="sidebarShare">
                        <i class="ri-settings-2-fill"></i> <span>@lang('Settings')</span>
                    </a>
                    <div class="collapse menu-dropdown {{ areActiveRoutesBool(['admin.updateTradingPrice', 'admin.setTaxRate', 'admin.setting.mail.create', 'admin.setting.sms.create']) ? 'show' : '' }}" id="sidebarSettings">
                    <div class="collapse menu-dropdown {{ areActiveRoutesBool(['admin.updateTradingPrice', 'admin.setTaxRate', 'admin.setting.mail.create', 'admin.setting.sms.create']) ? 'show' : '' }}" id="sidebarSettings">
                        <ul class="nav nav-sm flex-column">
                            @can('general-setting-view')
                            <li class="nav-item">
                                <a href="{{ route('admin.markets.index') }}" class="nav-link {{ areActiveRoutes(['admin.markets.index']) }}">@lang('Markets open/close')</a>
                            </li>
                            @endcan
                            @can('general-setting-view')
                            <li class="nav-item">
                                <a href="{{ route('admin.general-setting') }}" class="nav-link {{ areActiveRoutes(['admin.general-setting']) }}">@lang('General Setting')</a>
                            </li>
                            @endcan
                            @can('set-min-max-trading-amount-view')
                            <li class="nav-item">
                                <a href="{{ route('admin.updateTradingPrice') }}" class="nav-link {{ areActiveRoutes(['admin.updateTradingPrice']) }}">@lang('Set minimum/maximum trading amount')</a>
                            </li>
                            @endcan
                            @can('set-income-tax-rate-view')
                            <li class="nav-item">
                                <a href="{{ route('admin.setTaxRate') }}" class="nav-link {{ areActiveRoutes(['admin.setTaxRate']) }}">@lang('Set income tax rate')</a>
                            </li>
                            @endcan
                            @can('sms-api-page-view')
                            <li class="nav-item">
                                <a href="{{ route('admin.setting.sms.create') }}" class="nav-link {{ areActiveRoutes(['admin.setting.sms.create']) }}">@lang('Sms api settings')</a>
                            </li>
                            @endcan
                            @can('email-api-page-view')
                            <li class="nav-item">
                                <a href="{{ route('admin.setting.mail.create') }}" class="nav-link {{ areActiveRoutes(['admin.setting.mail.create']) }}">@lang('Email api settings')</a>
                            </li>
                            @endcan
                            @can('payments-api-page-view')
                            <li class="nav-item">
                                <a href="#" class="nav-link">@lang('Payment authentication apis')</a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcanAny
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
