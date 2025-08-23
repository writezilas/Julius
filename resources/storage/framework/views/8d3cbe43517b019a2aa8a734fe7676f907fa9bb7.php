<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="<?php echo e(route('admin.index')); ?>" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('assets/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('assets/images/autobidder_dark.png')); ?>" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="<?php echo e(route('admin.index')); ?>" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('assets/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('assets/images/autobidder_light.png')); ?>" alt="" height="17">
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
                <li class="menu-title"><span><?php echo app('translator')->get('translation.menu'); ?></span></li>
                <a class="nav-link menu-link" href="<?php echo e(route('admin.index')); ?>">
                   <i class="ri-home-2-fill"></i>  <span><?php echo app('translator')->get('translation.dashboard'); ?></span>
                </a>


                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarUsers" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo e(areActiveRoutesBool(['users.status'])); ?>" aria-controls="sidebarCustomer">
                        <i class="ri-user-2-fill"></i> <span><?php echo app('translator')->get('User Management'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown <?php echo e(areActiveRoutesBool(['users.status', 'admin.role.index', 'admin.role.permission', 'admin.staff.index', 'admin.staff.create', 'admin.staff.edit']) ? 'show' : ''); ?>" id="sidebarUsers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="#sidebarCustomer" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo e(areActiveRoutesBool(['users.status'])); ?>" aria-controls="sidebarUsers">
                                    <span><?php echo app('translator')->get('Customer Management'); ?></span>
                                </a>
                                <div class="collapse menu-dropdown <?php echo e(areActiveRoutesBool(['users.status']) ? 'show' : ''); ?>" id="sidebarCustomer">
                                    <ul class="nav nav-sm flex-column">



                                        <li class="nav-item">
                                            <a href="<?php echo e(url('users/block')); ?>" class="nav-link <?php echo e(Request::is('users/block') ? 'active' : ''); ?>"><?php echo app('translator')->get('Block Users'); ?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="<?php echo e(url('users/suspend')); ?>" class="nav-link <?php echo e(Request::is('users/suspend') ? 'active' : ''); ?>"><?php echo app('translator')->get('Suspend Users'); ?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="<?php echo e(url('users/fine')); ?>" class="nav-link <?php echo e(Request::is('users/fine') ? 'active' : ''); ?>"><?php echo app('translator')->get('Fine Users'); ?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="<?php echo e(url('users/all')); ?>" class="nav-link <?php echo e(Request::is('users/all') ? 'active' : ''); ?>"><?php echo app('translator')->get('All Users'); ?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.role.index')); ?>" class="nav-link <?php echo e(areActiveRoutes(['admin.role.index', 'admin.role.permission'])); ?>"><?php echo app('translator')->get('Role permissions'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.staff.index')); ?>" class="nav-link <?php echo e(areActiveRoutes(['admin.staff.index', 'admin.staff.create', 'admin.staff.edit'])); ?>"><?php echo app('translator')->get('Staffs'); ?></a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarShare"
                       data-bs-toggle="collapse"
                       role="button"
                       aria-expanded="<?php echo e(areActiveRoutesBool(['admin.allocate.share', 'admin.transfer.share', 'admin.allocate.share.history'])); ?>"
                       aria-controls="sidebarShare">
                        <i class="ri-share-box-fill"></i> <span><?php echo app('translator')->get('Share Managament'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown <?php echo e(areActiveRoutesBool(['admin.allocate.share', 'admin.transfer.share', 'admin.allocate.share.history']) ? 'show' : ''); ?>" id="sidebarShare">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.allocate.share')); ?>" class="nav-link <?php echo e(areActiveRoutes(['admin.allocate.share'])); ?>"><?php echo app('translator')->get('Allocate share to user'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.allocate.share.history')); ?>" class="nav-link <?php echo e(areActiveRoutes(['admin.allocate.share.history'])); ?>"><?php echo app('translator')->get('Allocate share history'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.transfer.share')); ?>" class="nav-link <?php echo e(areActiveRoutes(['admin.transfer.share'])); ?>"><?php echo app('translator')->get('Transfer share from user'); ?></a>
                            </li>



                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarCommunication"
                       data-bs-toggle="collapse" role="button"
                       aria-expanded="<?php echo e(areActiveRoutesBool(['announcement.create', 'email.create', 'admin.support'])); ?>"
                       aria-controls="sidebarCommunication"
                    >
                        <i class="ri-message-2-fill"></i> <span><?php echo app('translator')->get('Communications'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown <?php echo e(areActiveRoutesBool(['announcement.index', 'announcement.create', 'announcement.edit', 'email.create', 'admin.support']) ? 'show' : ''); ?>" id="sidebarCommunication">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?php echo e(route('announcement.index')); ?>" class="nav-link <?php echo e(areActiveRoutes(['announcement.create', 'announcement.index', 'announcement.edit'])); ?>"><?php echo app('translator')->get('Announcements'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('email.create')); ?>" class="nav-link <?php echo e(areActiveRoutes(['email.create'])); ?>"><?php echo app('translator')->get('Emails'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.support')); ?>" class="nav-link <?php echo e(areActiveRoutes(['admin.support'])); ?>"><?php echo app('translator')->get('Supports'); ?></a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarTrades"
                       data-bs-toggle="collapse" role="button"
                       aria-expanded="<?php echo e(areActiveRoutesBool(['admin.trade.index', 'admin.trade.create', 'admin.trade.edit', 'admin.period.index', 'admin.period.edit', 'admin.period.create'])); ?>"
                       aria-controls="sidebarCommunication"
                    >
                        <i class="ri-money-dollar-box-fill"></i> <span><?php echo app('translator')->get('Trades settings'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown <?php echo e(areActiveRoutesBool(['admin.trade.index', 'admin.trade.create', 'admin.trade.edit', 'admin.period.index', 'admin.period.edit', 'admin.period.create']) ? 'show' : ''); ?>" id="sidebarTrades">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.trade.index')); ?>" class="nav-link <?php echo e(areActiveRoutes(['admin.trade.index', 'admin.trade.create', 'admin.trade.edit'])); ?>"><?php echo app('translator')->get('Trades'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.period.index')); ?>" class="nav-link <?php echo e(areActiveRoutes(['admin.period.index', 'admin.period.edit', 'admin.period.create'])); ?>"><?php echo app('translator')->get('Trading periods'); ?></a>
                            </li>
                        </ul>
                    </div>
                </li>


                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarFrontEndSettings" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo e(areActiveRoutesBool(['policy.edit'])); ?>" aria-controls="sidebarShare">
                        <i class="ri-list-settings-fill"></i> <span><?php echo app('translator')->get('Frontend settings'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown <?php echo e(areActiveRoutesBool(['policy.edit']) ? 'show' : ''); ?>" id="sidebarFrontEndSettings">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?php echo e(route('policy.edit', 'how-it-work')); ?>" class="nav-link <?php echo e(Request::is('admin/policy/how-it-work') ? 'active' : ''); ?>"><?php echo app('translator')->get('How it works page'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('policy.edit', 'terms-and-conditions')); ?>" class="nav-link <?php echo e(Request::is('admin/policy/terms-and-conditions') ? 'active' : ''); ?>"><?php echo app('translator')->get('Terms and conditions'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('policy.edit', 'privacy-policy')); ?>" class="nav-link <?php echo e(Request::is('admin/policy/privacy-policy') ? 'active' : ''); ?>"><?php echo app('translator')->get('Privacy policy'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('policy.edit', 'confidentiality-policy')); ?>" class="nav-link <?php echo e(Request::is('admin/policy/confidentiality-policy') ? 'active' : ''); ?>"><?php echo app('translator')->get('Confidentiality policy'); ?></a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarSettings" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo e(areActiveRoutesBool(['admin.updateTradingPrice', 'admin.setTaxRate', 'admin.setting.mail.create', 'admin.setting.sms.create'])); ?>" aria-controls="sidebarShare">
                        <i class="ri-settings-2-fill"></i> <span><?php echo app('translator')->get('Settings'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown <?php echo e(areActiveRoutesBool(['admin.updateTradingPrice', 'admin.setTaxRate', 'admin.setting.mail.create', 'admin.setting.sms.create']) ? 'show' : ''); ?>" id="sidebarSettings">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.updateTradingPrice')); ?>" class="nav-link <?php echo e(areActiveRoutes(['admin.updateTradingPrice'])); ?>"><?php echo app('translator')->get('Set minimum/maximum trading amount'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.setTaxRate')); ?>" class="nav-link <?php echo e(areActiveRoutes(['admin.setTaxRate'])); ?>"><?php echo app('translator')->get('Set income tax rate'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.setting.sms.create')); ?>" class="nav-link <?php echo e(areActiveRoutes(['admin.setting.sms.create'])); ?>"><?php echo app('translator')->get('Sms api settings'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.setting.mail.create')); ?>" class="nav-link <?php echo e(areActiveRoutes(['admin.setting.mail.create'])); ?>"><?php echo app('translator')->get('Email api settings'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?php echo app('translator')->get('Payment authentication apis'); ?></a>
                            </li>
                        </ul>
                    </div>
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
<?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/layouts/sidebar.blade.php ENDPATH**/ ?>