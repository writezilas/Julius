<?php ($pageTitle = __('translation.support')); ?>
<?php $__env->startSection('title'); ?> <?php echo e($pageTitle); ?>  <?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

	<?php $__env->startComponent('components.breadcrumb'); ?>
		<?php $__env->slot('li_1'); ?> Pages <?php $__env->endSlot(); ?>
		<?php $__env->slot('title'); ?> <?php echo e($pageTitle); ?>  <?php $__env->endSlot(); ?>
	<?php echo $__env->renderComponent(); ?>

	 <div class="row justify-content-center mt-4">
        <div class="col-lg-5">
            <div class="text-center mb-4">
                <h4 class="fw-semibold fs-22">Telegram Channel</h4>
                <p class="text-muted mb-2 fs-15">Join our Telegram channel here:</p>
                <p class="text-muted mb-2 fs-15"><a href="http://127.0.0.1:8000/support">http://127.0.0.1:8000/support</a></p>
            </div>
        </div><!--end col-->
    </div><!--end row-->
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Talk to Us</h4>
                </div><!-- end card header -->
                <div class="card-body">
                    <div class="live-preview">
                        <div class="row gy-4">
                        	<form class="needs-validation" novalidate method="POST" action="<?php echo e(route('supports.store')); ?>">
                                <?php echo csrf_field(); ?>
	                            <div class="col-xxl-12 col-md-12 mb-3">
	                                <div class="form-floating">
	                                    <input type="text" class="form-control" id="first_name" name="first_name"
	                                        placeholder="Enter your firstname" required>
	                                    <label for="first_name">First Name <span
                                                    class="text-danger">*</span></label>
                                        <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-feedback" role="alert">
                                                <strong><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <div class="invalid-feedback">
                                            Please enter first name
                                        </div>
	                                </div>
	                            </div>
	                            <div class="col-xxl-12 col-md-12 mb-3">
	                                <div class="form-floating">
	                                    <input type="text" class="form-control" id="last_name" name="last_name"
	                                        placeholder="Enter your lastname" required>
	                                    <label for="last_name">Last Name <span
                                                    class="text-danger">*</span></label>
                                        <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-feedback" role="alert">
                                                <strong><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <div class="invalid-feedback">
                                            Please enter last name
                                        </div>
	                                </div>
	                            </div>
	                            <div class="col-xxl-12 col-md-12 mb-3">
	                                <div class="form-floating">
	                                    <input type="email" class="form-control" id="email" name="email"
	                                        placeholder="Enter your email" required>
	                                    <label for="email">Email <span
                                                    class="text-danger">*</span></label>
                                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-feedback" role="alert">
                                                <strong><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <div class="invalid-feedback">
                                            Please enter email
                                        </div>
	                                </div>
	                            </div>
	                            <div class="col-xxl-12 col-md-12 mb-3">
	                                <div class="form-floating">
	                                    <input type="text" class="form-control" id="username" name="username"
	                                        placeholder="Enter your username">
	                                    <label for="username">Username <span
                                                    class="text-dark">(Optional)</span></label>
                                        <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-feedback" role="alert">
                                                <strong><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <div class="invalid-feedback">
                                            Please enter username
                                        </div>
	                                </div>
	                            </div>

	                            <div class="col-xxl-12 col-md-12 mb-3">
	                                <div class="form-floating">
	                                    <input type="number" class="form-control" id="telephone" name="telephone"
	                                        placeholder="Enter your telephone" required>
	                                    <label for="telephone">Telephone <span
                                                    class="text-danger">*</span></label>
                                        <?php $__errorArgs = ['telephone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-feedback" role="alert">
                                                <strong><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <div class="invalid-feedback">
                                            Please enter telephone
                                        </div>
	                                </div>
	                            </div>
	                            <div class="col-xxl-12 col-md-12 mb-3">
	                                <div class="form-floating">
	                                    <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
	                                    <label for="message">Message <span
                                                    class="text-danger">*</span></label>
                                        <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-feedback" role="alert">
                                                <strong><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <div class="invalid-feedback">
                                            Please enter message
                                        </div>
                                        <div id="messageHelpBlock" class="form-text">
                                        	Limit word to 150 words
                                        </div>
	                                </div>
	                            </div>
	                            
	                             <div class="mt-4">
                                    <button class="btn btn-success w-100" type="submit">Submit</button>
                                </div>
	                          </form>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
	<script src="<?php echo e(URL::asset('/assets/js/app.min.js')); ?>"></script>
	<script src="<?php echo e(URL::asset('assets/js/pages/form-validation.init.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/user-panel/support.blade.php ENDPATH**/ ?>