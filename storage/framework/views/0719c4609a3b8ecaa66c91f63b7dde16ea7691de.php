<?php $__env->startSection('title'); ?> <?php echo e($pageTitle); ?>  <?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(URL::asset('assets/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" type="text/css" />
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
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Status</th>
                                
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
                                        <select class="form-control form-control-sm" 
                                            id="status-update" 
                                            <?php if($user->role_id == 1): echo 'disabled'; endif; ?>
                                            data-id=<?php echo e($user->id); ?>>
                                            <option value="pending" <?php if($user->status == 'pending'): echo 'selected'; endif; ?>>Pending</option>
                                            <option value="block" <?php if($user->status == 'block'): echo 'selected'; endif; ?>>Block</option>
                                            <option value="suspend" <?php if($user->status == 'suspend'): echo 'selected'; endif; ?>>Suspend</option>
                                            <option value="fine" <?php if($user->status == 'fine'): echo 'selected'; endif; ?> >Fine</option>
                                        </select>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script src="<?php echo e(URL::asset('assets/js/pages/datatables.init.js')); ?>"></script>

    <script src="<?php echo e(URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('/assets/js/pages/sweetalerts.init.js')); ?>"></script>

    <script src="<?php echo e(URL::asset('/assets/js/app.min.js')); ?>"></script>

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

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u128770841/domains/autobider.live/resources/views/admin-panel/users/index.blade.php ENDPATH**/ ?>