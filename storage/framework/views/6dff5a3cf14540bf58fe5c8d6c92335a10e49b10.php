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
                </div>
                <div class="card-body">
                    <form method="POST" action="#" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mail Host *</label>
                                    <input type="text" name="mail_host" class="form-control" value="" required="">
                                </div>
                                <div class="form-group mt-2">
                                    <label>Mail Address *</label>
                                    <input type="text" name="mail_address" class="form-control" value="" required="">
                                </div>
                                <div class="form-group mt-2">
                                    <label>Mail From Name *</label>
                                    <input type="text" name="mail_name" class="form-control" value="" required="">
                                </div>
                                <div class="form-group mt-2">
                                    <input type="submit" value="Submit" class="btn btn-primary">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mail Port *</label>
                                    <input type="text" name="port" class="form-control" value="465" required="">
                                </div>
                                <div class="form-group mt-2">
                                    <label>Password *</label>
                                    <input type="password" name="password" class="form-control" value="" required="">
                                </div>
                                <div class="form-group mt-2">
                                    <label>Encryption *</label>
                                    <input type="text" name="encryption" class="form-control" value="ssl" required="">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>

    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/admin-panel/settings/email.blade.php ENDPATH**/ ?>