<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('assets/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('assets/images/autobidder_dark.png')); ?>" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index" class="logo logo-light">
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
                <a class="nav-link menu-link" href="<?php echo e(url('/')); ?>">
                   <i class="ri-apps-2-line"></i>  <span><?php echo app('translator')->get('translation.dashboard'); ?></span>
                </a>


                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarCustomer" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCustomer">
                        <i class="ri-apps-2-line"></i> <span><?php echo app('translator')->get('Customer Management'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarCustomer">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="#sidebarUsers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarUsers">
                                    <span><?php echo app('translator')->get('User Management'); ?></span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarUsers">
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
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarShare" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarShare">
                        <i class="ri-apps-2-line"></i> <span><?php echo app('translator')->get('Share Managament'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarShare">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="#sidebarUsers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarUsers">
                                    <span><?php echo app('translator')->get('User Management'); ?></span>
                                </a>
                                <div class="collapse menu-dropdown" id="sidebarUsers">
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
                        </ul>
                    </div>
                </li>
                <li class="menu-title"><i class="ri-more-fill"></i> <span><?php echo app('translator')->get('Settings'); ?></span></li>


            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
<?php /**PATH /home/u128770841/domains/autobider.live/resources/views/layouts/sidebar.blade.php ENDPATH**/ ?>