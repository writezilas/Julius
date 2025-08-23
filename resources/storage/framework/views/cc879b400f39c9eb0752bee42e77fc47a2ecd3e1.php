<?php $__env->startComponent('mail::message'); ?>

**<?php echo e(__('Hi')); ?> <?php echo e($payment->sender->username); ?>,**

<?php echo e($payment->receiver->username); ?> <?php echo e(__('  has confirmed your payment of ')); ?> <?php echo e(formatPrice($payment->amount)); ?> <?php echo e(__(' The payment id is ')); ?> <?php echo e($payment->txs_id); ?>


<?php $__env->startComponent('mail::table'); ?>
    | <!-- -->    | <!-- -->    |
    |-------------|-------------|
    | <?php echo e(__('Note from the Seller')); ?>: | <?php echo e($payment->note_by_receiver); ?> |

<?php echo $__env->renderComponent(); ?>

<?php $__env->startComponent('mail::button', ['url' => route('bought-share.view',$payment->user_share_id)]); ?>
    <?php echo e(__('View Order')); ?>

<?php echo $__env->renderComponent(); ?>

<?php echo e(__('Thank You')); ?>,<br>
<?php echo e(env('APP_NAME')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/emails/payment-approved.blade.php ENDPATH**/ ?>