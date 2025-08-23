<?php ($pageTitle = 'Refferal Code'); ?>
<?php $__env->startSection('title'); ?> <?php echo e($pageTitle); ?>  <?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
	<?php $__env->startComponent('components.breadcrumb'); ?>
		<?php $__env->slot('li_1'); ?> Pages <?php $__env->endSlot(); ?>
		<?php $__env->slot('title'); ?>  <?php echo e($pageTitle); ?> <?php $__env->endSlot(); ?>
	<?php echo $__env->renderComponent(); ?>
	<div class="row justify-content-center mt-4">
        <div class="col-lg-5">
            <div class="text-center mb-4">
                <h4 class="fw-semibold fs-22">Your referral link</h4>
                <p class="text-muted mb-2 fs-15">Copy Below link</p>
                <p class="text-muted mb-2 fs-15 mt-3">
                    <a href="javascript:;" id="referralCode"><?php echo e(url('/register?refferal_code='.auth()->user()->username)); ?></a>
                </p>

                <button type="button" class="btn btn-link" onclick="handleCopyLink()">Copy link</button>
            </div>
        </div><!--end col-->
    </div><!--end row-->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>
        function handleCopyLink() {
            let text = document.getElementById("referralCode");
            // console.log("text", );
            navigator.clipboard.writeText(text.innerText);
            alert("Referral link copied in clipboard");
        }
    </script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/user-panel/refferals.blade.php ENDPATH**/ ?>