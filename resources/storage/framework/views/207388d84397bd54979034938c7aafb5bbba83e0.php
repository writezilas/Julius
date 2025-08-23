<?php $__env->startComponent('mail::message'); ?>

**<?php echo e(__('Hi')); ?> <?php echo e($payment->receiver->username); ?>,**

<?php echo e(__('You have received a payment from')); ?> <?php echo e($payment->sender->username); ?>


<?php $__env->startComponent('mail::table'); ?>
    | <!-- -->    | <!-- -->    |
    |-------------|-------------|
    | <?php echo e(__('Sender Name')); ?>: | <?php echo e(json_decode($payment->sender->business_profile)->mpesa_name); ?> |
    | <?php echo e(__('Sender M-PESA no')); ?>: | <?php echo e($payment->number); ?> |
    | <?php echo e(__('Amount Sent')); ?>:  | <?php echo e(formatPrice($payment->amount)); ?> |
    | <?php echo e(__('Transaction Id')); ?>: | <?php echo e($payment->txs_id); ?> |
    | <?php echo e(__('Note From Buyer')); ?>: | <?php echo e($payment->note_by_sender); ?> |

<?php echo $__env->renderComponent(); ?>

<?php $__env->startComponent('mail::button', ['url' => route('sold-share.view',$payment->paired->paired_user_share_id)]); ?>
    <?php echo e(__('View payment')); ?>

<?php echo $__env->renderComponent(); ?>

<?php echo e(__('Thank You')); ?>,<br>
<?php echo e(env('APP_NAME')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/emails/payment-to-seller.blade.php ENDPATH**/ ?>