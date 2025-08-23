<?php $__env->startSection('title'); ?> <?php echo e($pageTitle); ?>  <?php $__env->stopSection(); ?>
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
                    <table class="table table-bordered">
                        <tr>
                            <th>Name</th>
                            <td><?php echo e($user->name); ?></td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td><?php echo e($user->username); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo e($user->email); ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?php echo e($user->phone); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><?php echo e($user->status === 'pending' ? 'Active' : $user->status); ?></td>
                        </tr>
                        <tr>
                            <th>Mpesa name</th>
                            <td><?php echo e(json_decode($user->business_profile)->mpesa_name); ?></td>
                        </tr>
                        <tr>
                            <th>Mpesa no</th>
                            <td><?php echo e(json_decode($user->business_profile)->mpesa_no); ?></td>
                        </tr>
                        <tr>
                            <th>Mpesa till no</th>
                            <td><?php echo e(json_decode($user->business_profile)->mpesa_till_no); ?></td>
                        </tr>
                        <tr>
                            <th>Mpesa till name</th>
                            <td><?php echo e(json_decode($user->business_profile)->mpesa_till_name); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">User status update</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <form action="<?php echo e(route('user.status.update', $user->id)); ?>" method="post">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <p class="m-0 p-0">User status</p>
                                        <label>
                                            <input type="radio" name="status" value="block" <?php if($user->status === 'block'): echo 'checked'; endif; ?>>
                                            <span>Block</span>
                                            <?php if($user->block_until): ?>
                                                <span class="text-warning italic">
                                                   untill  <?php echo e(\Carbon\Carbon::parse($user->block_until)->diffForHumans()); ?>

                                                </span>
                                            <?php endif; ?>
                                        </label>
                                        <br>
                                        <label>
                                            <input type="radio" name="status" value="pending" <?php if($user->status === 'pending'): echo 'checked'; endif; ?>>
                                            <span>Unblock</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Block time (hours)</label>
                                        <input type="number" class="form-control" name="time">
                                        <?php if($user->block_until): ?>
                                            <span class="text-warning italic">
                                                Block until: <?php echo e(\Carbon\Carbon::parse($user->block_until)->diffForHumans()); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </div>
                        </form>
                    </table>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/admin-panel/users/view.blade.php ENDPATH**/ ?>