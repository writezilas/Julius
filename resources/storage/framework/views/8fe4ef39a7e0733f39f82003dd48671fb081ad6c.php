<?php $__env->startSection('title'); ?> <?php echo e($pageTitle); ?>  <?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> <?php echo app('translator')->get('translation.dashboard'); ?> <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> <?php echo e($pageTitle); ?> <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo e($pageTitle); ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="alternative-pagination" class="table  align-middle table-hover table-bordered" style="width:100%">
                            <thead>
                            <tr>
                                <th>SR No.</th>
                                <th>Ticket no</th>
                                <th>Share type</th>
                                <th>Date</th>
                                <th>Share quantity</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Time remaining</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__currentLoopData = $boughtShares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $share): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td><?php echo e($share->ticket_no); ?></td>
                                    <td><?php echo e($share->trade->name); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($share->created_at)->format('d M Y')); ?></td>
                                    <td><?php echo e($share->total_share_count); ?></td>
                                    <td><?php echo e($share->amount); ?></td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo e($share->status); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if($share->status === 'failed'): ?>
                                           Time Expired
                                        <?php elseif($share->status === 'completed' && $share->start_date != ''): ?>
                                            Payment completed
                                        <?php else: ?>
                                            <p id="timer<?php echo e($share->id); ?>"></p>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                           <a href="<?php echo e(route('bought-share.view', $share->id)); ?>" class="btn btn-info">Details</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>

<script>
    <?php $__currentLoopData = $boughtShares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $singleShare): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($singleShare->status != 'failed'): ?>
            // Set the date we're counting down to
            getCounterTime('<?php echo e(\Carbon\Carbon::parse($singleShare->created_at)->addHour(3)); ?>', "timer"+<?php echo e($singleShare->id); ?>, <?php echo e($singleShare->id); ?>)
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    function getCounterTime(startTime, id, shareId) {
        var countDownDate = new Date(startTime).getTime();

        // Update the count down every 1 second
        var x = setInterval(function() {

            // Get today's date and time
            var now = new Date().getTime();

            // Find the distance between now and the count down date
            var distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Output the result in an element with id="demo"
            document.getElementById(id).innerHTML = days + "d " + hours + "h "
                + minutes + "m " + seconds + "s ";

            // If the count down is over, write some text
            if (distance < 0) {
                clearInterval(x);
                document.getElementById(id).innerHTML = "EXPIRED";

                $.post('<?php echo e(route('share.status.updateAsFailed')); ?>', {_token:'<?php echo e(csrf_token()); ?>', id: shareId, status: 'failed'});
            }
        }, 1000);
    }

</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/user-panel/bought-shares.blade.php ENDPATH**/ ?>