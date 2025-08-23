<?php $__env->startSection('title'); ?> <?php echo e($pageTitle); ?>  <?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> <?php echo app('translator')->get('translation.dashboard'); ?> <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> <?php echo e($pageTitle); ?> <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0"><?php echo e($pageTitle); ?></h5>
                    <a href="<?php echo e(route('announcement.create')); ?>" class="btn btn-primary">
                        <i class="ri-add-box-fill"></i> New
                    </a>
                </div>
                <div class="card-body">
                   <div class="table-responsive">
                       <table id="alternative-pagination" class="table dt-responsive align-middle table-hover table-bordered" style="width:100%">
                           <thead>
                           <tr>
                               <th>Sl</th>
                               <th>title</th>
                               <th>Sort description</th>
                               <th>Description</th>
                               <th>Created at</th>
                               <th>Action</th>
                           </tr>
                           </thead>
                           <tbody>
                           <?php $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                               <tr>
                                   <td><?php echo e($loop->iteration); ?></td>
                                   <td><?php echo e($announcement->title); ?></td>
                                   <td><?php echo e($announcement->excerpt); ?></td>
                                   <td><?php echo $announcement->description; ?></td>
                                   <td><?php echo e($announcement->created_at->diffForHumans()); ?></td>

                                   <td>
                                       <a href="<?php echo e(route('announcement.edit', $announcement->id)); ?>" class="btn btn-sm btn-success">
                                           Edit
                                       </a>
                                       <a href="<?php echo e(route('announcement.delete', $announcement->id)); ?>" class="btn btn-sm btn-danger delete_two">
                                           delete
                                       </a>
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

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/admin-panel/communications/announcement/index.blade.php ENDPATH**/ ?>