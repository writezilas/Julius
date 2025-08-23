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
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>

                                 <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td><?php echo e($user->name); ?></td>
                                    <td><?php echo e($user->email); ?></td>
                                    <td><?php echo e($user->role->name); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($user->created_at)); ?></td>
                                    <td>









                                        <a href="<?php echo e(route('user.single', $user->id)); ?>" class="btn btn-primary">View</a>
                                    </td>
                                    
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr class="odd">
                                    <td valign="top" colspan="7" class="dataTables_empty">
                                        <?php echo e($emptyMessage); ?>

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
<?php $__env->startSection('script'); ?>

    <script type="text/javascript">
        $(document).on('change', '#status-update', function(e) {
            e.preventDefault();
            $this = $(this);
            $id = $(this).attr('data-id');
            Swal.fire({
                title: "Are you sure?",
                text: "You want to update the status",
                icon: "warning",
                showCancelButton: true,
                confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
                cancelButtonClass: 'btn btn-danger w-xs mt-2',
                confirmButtonText: "Yes, delete it!",
                buttonsStyling: false,
                showCloseButton: true
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        type: "POST",
                        url: "<?php echo e(url('users/_id/status-update')); ?>".replace('_id', $id),
                        data:{
                            status:$this.val(),
                            _token: "<?php echo e(csrf_token()); ?>",
                        },
                        success: function(){
                            Swal.fire({
                                title: 'Updated!',
                                text: 'Your status has been updated.',
                                icon: 'success',
                                confirmButtonClass: 'btn btn-primary w-xs mt-2',
                                buttonsStyling: false
                            }).then(function (result) {
                                window.location.reload();
                            });
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            console.log("Status: " + textStatus);
                            console.log("Error: " + errorThrown);
                        }
                    });
                }
            });

        })
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/admin-panel/users/index.blade.php ENDPATH**/ ?>