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
                    <table id="alternative-pagination" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>SR No.</th>
                                <th>Share type</th>
                                <th>Username</th>
                                <th>Share quantity</th>
                                <th>Allocate At</th>
                                 <th>Action</th>
                            </tr>
                            </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $allocateShares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $share): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($share->userShare->trade->name); ?></td>
                                <td><?php echo e($share->userShare->user->username); ?></td>
                                <td><?php echo e($share->shares); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($share->created_at)); ?></td>
                                 <td>



                                     <a href="<?php echo e(route('admin.allocate.share.destroy', $share->user_share_id)); ?>" onclick="return confirm('Are you sure want to remove the allocate share date? you can not restore it.')" class="btn btn-sm btn-danger">
                                         Delete
                                     </a>
                                 </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr class="odd">
                                <td valign="top" colspan="7" class="dataTables_empty">
                                    No data found
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/admin-panel/share-management/allocate-share-history.blade.php ENDPATH**/ ?>