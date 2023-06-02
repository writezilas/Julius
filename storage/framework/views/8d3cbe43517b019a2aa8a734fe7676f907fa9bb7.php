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
                    <a class="nav-link menu-link" href="#sidebarUsers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCustomer">
                        <i class="ri-user-2-fill"></i> <span><?php echo app('translator')->get('User Management'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarUsers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="#sidebarCustomer" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarUsers">
                                    <span><?php echo app('translator')->get('Customer Management'); ?></span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarCustomer">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="<?php echo e(url('users/pending')); ?>" class="nav-link"><?php echo app('translator')->get('Pending Users'); ?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="<?php echo e(url('users/block')); ?>" class="nav-link"><?php echo app('translator')->get('Block Users'); ?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="<?php echo e(url('users/suspend')); ?>" class="nav-link"><?php echo app('translator')->get('Suspend Users'); ?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="<?php echo e(url('users/fine')); ?>" class="nav-link"><?php echo app('translator')->get('Fine Users'); ?></a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="<?php echo e(url('users/all')); ?>" class="nav-link"><?php echo app('translator')->get('All Users'); ?></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.role.index')); ?>" class="nav-link"><?php echo app('translator')->get('Role permissions'); ?></a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarShare" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarShare">
                        <i class="ri-share-box-fill"></i> <span><?php echo app('translator')->get('Share Managament'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarShare">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?php echo app('translator')->get('Allocate share to user'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?php echo app('translator')->get('Transfer share from user'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?php echo app('translator')->get('Add/remove servers to dashboard'); ?></a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarCommunication" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarShare">
                        <i class="ri-message-2-fill"></i> <span><?php echo app('translator')->get('Communications'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarCommunication">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?php echo app('translator')->get('Announcements'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?php echo app('translator')->get('Emails'); ?></a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarSettings" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarShare">
                        <i class="ri-settings-2-fill"></i> <span><?php echo app('translator')->get('Settings'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarSettings">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?php echo app('translator')->get('Set trading period'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?php echo app('translator')->get('Set value of each share being bought'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?php echo app('translator')->get('set value of each share being sold'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?php echo app('translator')->get('Set minimum trading amount'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?php echo app('translator')->get('Set maximum trading amount per transaction'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link"><?php echo app('translator')->get('Set income tax rate'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.setting.sms.create')); ?>" class="nav-link"><?php echo app('translator')->get('Sms api settings'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('admin.setting.mail.create')); ?>" class="nav-link"><?php echo app('translator')->get('Email api settings'); ?></a>
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