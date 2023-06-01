<?php ($pageTitle = 'How it works'); ?>
<?php $__env->startSection('title'); ?> <?php echo e($pageTitle); ?>  <?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
	<?php $__env->startComponent('components.breadcrumb'); ?>
		<?php $__env->slot('li_1'); ?> Pages <?php $__env->endSlot(); ?>
		<?php $__env->slot('title'); ?> <?php echo e($pageTitle); ?>  <?php $__env->endSlot(); ?>
	<?php echo $__env->renderComponent(); ?>
	<div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <div class="flex-grow-1">
                        <h4 class="card-title mb-0">How To Bid (Buy Shares)</h4>
                    </div>
                </div><!-- end card header -->

                <div class="card-body">
                    <p class="text-muted m-0">AFTER YOUR PAYMENT IS CONFIRMED, YOUR SHARES WILL START TO COUNT ON THE SOLD SHARES PAGE.</p>
                    <p class="text-muted ">ONCE YOUR SHARES MATURE, YOU WILL RECIEVE PAYMENT IMMEDIATELY.</p>
                    <p class="">THIS IS HOW YOU WILL GET YOUR PROFIT.</p>
                    <p class="text-muted m-0">3 days earns a profit of 30%</p>
                    <p class="text-muted m-0">6 days earns a profit of 60%</p>
                    <p class="text-muted m-0">7 days earns a profit of 70%</p>
                    <p class="text-muted m-0">8 days earns a profit of 80%</p>
                    <p class="text-muted">10 days earns a profit of 95%</p>
                    <p class="">TAKE AN EXAMPLE OF 10,000</p>
                    <p class="text-muted m-0">10,000 FOR 3 DAYS GET 13,000</p>
                    <p class="text-muted m-0">10,000 FOR 6 DAYS GET 16,000</p>
                    <p class="text-muted m-0">10,000 FOR 8 DAYS GET 17,000</p>
                    <p class="text-muted m-0">10,000 FOR 10 DAYS GET 19,500</p>
                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <div class="flex-grow-1">
                        <h4 class="card-title mb-0">How Do We Benefit From All These??</h4>
                    </div>
                </div><!-- end card header -->

                <div class="card-body">
                    <ul>
                    	<li>We get donations from members</li>
                    	<li>We also buy and sell shares like every other members</li>
                    </ul>

                </div><!-- end card-body -->
            </div><!-- end card -->
        </div>
        <!-- end col -->
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script src="<?php echo e(URL::asset('/assets/js/app.min.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u128770841/domains/autobider.live/resources/views/user-panel/how-it-works.blade.php ENDPATH**/ ?>