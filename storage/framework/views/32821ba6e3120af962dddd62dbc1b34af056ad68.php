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
                <h5 class="card-title mb-0"> <?php echo e($pageTitle); ?> </h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roleCreateModal">
                    <i class="ri-add-box-fill"></i> New
                </button>
            </div>
            <div class="card-body">
                <table id="alternative-pagination" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                    <thead>
                    <tr>
                        <th>SR No.</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($key + 1); ?></td>
                            <td><?php echo e($role->name); ?></td>
                             <td>
                                 <a href="<?php echo e(route('admin.role.permission', $role->id)); ?>" class="btn btn-sm btn-soft-primary" >
                                     <i class="ri-settings-3-fill"></i>
                                 </a>
                                 <a href="<?php echo e(route('admin.role.delete', $role->id)); ?>" class="btn btn-sm btn-soft-danger delete_two">
                                     <i class="ri-delete-bin-5-line"></i>
                                 </a>
                                 <button class="btn btn-sm btn-soft-success" data-bs-toggle="modal" data-bs-target="#roleEditModal<?php echo e($role->id); ?>">
                                     <i class="ri-edit-2-fill"></i>
                                 </button>
                             </td>
                        </tr>
                        <div class="modal fade" id="roleEditModal<?php echo e($role->id); ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Role create/edit</h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="<?php echo e(route('admin.role.update', $role->id)); ?>" method="post">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <div class="form-group">
                                                <label>Role name</label>
                                                <input type="text" class="form-control" placeholder="Role name" name="name" value="<?php echo e($role->name); ?>">
                                            </div>
                                            <div class="modal-footer mt-4">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr class="odd">
                            <td valign="top" colspan="7" class="dataTables_empty">
                                <?php echo e($emptyMessage); ?>

                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <?php echo e($roles->links()); ?>

            </div>
        </div>
    </div>
    <!--end col-->
</div>

<div class="modal fade" id="roleCreateModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Role create/edit</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?php echo e(route('admin.role.store')); ?>" method="post">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label>Role name</label>
                        <input type="text" class="form-control" placeholder="Role name" name="name" value="<?php echo e(old('name')); ?>">
                    </div>
                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/admin-panel/roles/index.blade.php ENDPATH**/ ?>