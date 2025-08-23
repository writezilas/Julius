<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="<?php echo e(route('user.dashboard')); ?>" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('assets/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('assets/images/autobidder_dark.png')); ?>" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="<?php echo e(route('user.dashboard')); ?>" class="logo logo-light">
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
                <li class="nav-item">
                    <a class="nav-link menu-link" href="<?php echo e(route('user.dashboard')); ?>">
                        <span><?php echo app('translator')->get('translation.dashboard'); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="<?php echo e(route('users.sold_shares')); ?>">
                        <span><?php echo app('translator')->get('translation.soldshares'); ?></span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="<?php echo e(route('users.bought_shares')); ?>">
                        <span><?php echo app('translator')->get('translation.boughtshares'); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="<?php echo e(route('users.referrals')); ?>">
                        <span><?php echo app('translator')->get('translation.refferals'); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="<?php echo e(route('page.how_it_work')); ?>">
                        <span><?php echo app('translator')->get('translation.how_it_works'); ?></span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link" href="<?php echo e(route('users.support')); ?>">
                        <span><?php echo app('translator')->get('translation.support'); ?></span>
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
<?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/user-panel/partials/sidebar.blade.php ENDPATH**/ ?>