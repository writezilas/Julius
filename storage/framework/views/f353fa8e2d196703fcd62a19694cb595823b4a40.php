<?php ($pageTitle = __('translation.soldshares') . ' Info'); ?>
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
                    <table id="alternative-pagination" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>SR No.</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Expected Return</th>
                                <th>Expense</th>
                                <th>Status</th>
                                <th>Authentication</th>
                                <th>Time remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($i=1; $i <= 200; $i++): ?>
                                <tr>
                                    <td><?php echo e($i); ?></td>
                                    <td>2022-01-10</td>
                                    <td>2000</td>
                                    <td>2022-01-10</td>
                                    <td>20</td>
                                    <td><button class="btn btn-sm btn-soft-info">Paring</button></td>
                                    <td>Yes</td>
                                    <td>1 hour 20 min remaining</td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/user-panel/sold-shares.blade.php ENDPATH**/ ?>