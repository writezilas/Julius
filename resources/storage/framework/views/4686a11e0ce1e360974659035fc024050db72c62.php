<?php $__env->startSection('title'); ?> <?php echo app('translator')->get('translation.dashboard'); ?> <?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Home <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Dashboard <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row project-wrapper">
        <div class="col-xxl-8">
            <div class="row">
                <?php
                    $investment = \App\Models\UserShare::where('status', 'completed')->where('user_id', auth()->user()->id)->sum('amount');
                    $profit = \App\Models\UserShare::where('status', 'completed')->where('user_id', auth()->user()->id)->sum('profit_share');
                ?>
                <div class="col-xl-3">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span
                                        class="avatar-title bg-soft-primary text-primary rounded-2 fs-2">
                                        <i data-feather="briefcase" class="text-primary"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden ms-3">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-3">
                                        Investment
                                    </p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0">
                                            <span class="counter-value" data-target="<?php echo e($investment); ?>">
                                                0
                                            </span>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

                <div class="col-xl-3">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span
                                        class="avatar-title bg-soft-warning text-warning rounded-2 fs-2">
                                        <i data-feather="award" class="text-warning"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-uppercase fw-medium text-muted mb-3">Earning</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="<?php echo e($profit); ?>">0</span></h4>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

                <div class="col-xl-3">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-info text-info rounded-2 fs-2">
                                        <i data-feather="clock" class="text-info"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden ms-3">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-3">
                                        Expense
                                    </p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="0">0</span></h4>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

                <div class="col-xl-3">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-info text-info rounded-2 fs-2">
                                        <i data-feather="clock" class="text-info"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden ms-3">
                                    <p class="text-uppercase fw-medium text-muted text-truncate mb-3">
                                        Referrals</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <h4 class="fs-4 flex-grow-1 mb-0"><span class="counter-value"
                                                data-target="0">0</span></h4>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end card body -->
                    </div>
                </div><!-- end col -->

                <?php
                    $trades = \App\Models\Trade::whereStatus('1')->get();
                ?>
                <?php $__currentLoopData = $trades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-xl-4">

                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-soft-info text-info rounded-2 fs-2">
                                        <i data-feather="clock" class="text-info"></i>
                                    </span>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden ms-3">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-3">
                                            Available <?php echo e($trade->name); ?>

                                        </p>
                                        <div class="d-flex align-items-center mb-3">
                                            <h4 class="fs-4 flex-grow-1 mb-0">
                                                <span class="counter-value" data-target="<?php echo e(checkAvailableSharePerTrade($trade->id)); ?>">0</span>
                                            </h4>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


            </div><!-- end row -->
        </div><!-- end col -->

        <div class="col-xxl-4">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Earning</h4>
                </div><!-- end card header -->

                <div class="card-body p-0 pb-2">
                    <div class="w-100">
                        <div id="customer-daily-earning-chart"
                             data-colors='["--vz-primary", "--vz-success", "--vz-danger"]'
                             class="apex-charts" dir="ltr"></div>
                    </div>
                </div><!-- end card body -->
            </div>
        </div>


    </div><!-- end row -->

    <?php echo $__env->make('components.trades', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- end row -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Activities</h4>
















                </div><!-- end card header -->
                <div class="card-body p-0">
                    <div data-simplebar style="height: 390px;">
                        <div class="p-3">

                            <?php
                                if(auth()->check()) {
                                    $logs = auth()->user()->logs->sortByDesc('id');
                                }
                            ?>

                            <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fs-14 mb-1"><?php echo e($log->remarks); ?></h6>
                                        <p class="text-muted fs-12 mb-0">
                                            <i class="mdi mdi-clock text-success fs-15 align-middle"></i>
                                            <?php echo e($log->created_at->diffForHumans()); ?>

                                        </p>
                                    </div>
                                    <div class="flex-shrink-0 text-end">
                                        <h6 class="mb-1 text-success">
                                            <?php echo e($log->value); ?> <?php echo e($log->type == 'share' ? 'share' : 'KSH'); ?>

                                        </h6>

                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>







                        </div>

                    </div>
                </div><!-- end cardbody -->
            </div><!-- end card -->
        </div>
        <div class="col-xl-6">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Announcements</h4>
                </div><!-- end card header -->

                <div class="card-body">
                    <div class="table-card">
                        <table class="table table-centered table-hover align-middle mb-0">
                            <tbody>
                            <?php
                                $announcements = \App\Models\Announcement::where('status', '1')->orderBy('id', 'desc')->paginate(5);
                            ?>

                            <?php $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div>
                                            <h4 class="m-0 p-0"><?php echo e($announcement->title); ?></h4>
                                            <small class="my-3">
                                                <?php echo e($announcement->created_at->diffForHumans()); ?>

                                            </small>
                                            <p>
                                                <?php echo e($announcement->excerpt); ?>

                                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#announcementModal<?php echo e($announcement->id); ?>">View details</a>
                                            </p>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="announcementModal<?php echo e($announcement->id); ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header border-bottom">
                                                <h5 class="modal-title" id="exampleModalLabel">
                                                    <?php echo e($announcement->title); ?>

                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                               <div>
                                                   <?php echo $announcement->description; ?>

                                               </div>
                                              <?php if($announcement->image): ?>
                                                <div class="announce-image mt-2">
                                                    <img src="<?php echo e(asset($announcement->image)); ?>" alt="<?php echo e($announcement->name); ?>" width="100%">
                                                </div>
                                              <?php endif; ?>
                                            <?php if($announcement->video_url): ?>
                                                <div class="announce-video mt-2">
                                                    <iframe width="100%" height="315"
                                                            src="<?php echo e($announcement->video_url); ?>">
                                                    </iframe>
                                                </div>
                                            <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


                            </tbody>
                        </table><!-- end table -->
                        <div class="float-end mt-2">
                            <?php echo e($announcements->links()); ?>

                        </div>
                    </div>
                </div> <!-- .card-body-->
            </div> <!-- .card-->
        </div>
    </div><!-- end row -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <!-- apexcharts -->
    <script src="<?php echo e(URL::asset('/assets/libs/apexcharts/apexcharts.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('/assets/libs/jsvectormap/jsvectormap.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('assets/libs/swiper/swiper.min.js')); ?>"></script>
    <!-- dashboard init -->
    <script src="<?php echo e(URL::asset('/assets/js/pages/dashboard-ecommerce.init.js')); ?>"></script>
























































































































































<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/user-panel/dashboard.blade.php ENDPATH**/ ?>