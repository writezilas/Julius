<?php $__env->startSection('title'); ?> <?php echo e($pageTitle); ?>  <?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>

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
                    <h5 class="card-title mb-0">Your paired shares</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>SI</th>
                                    <th>Buyer name</th>
                                    <th>Buyer username</th>
                                    <th>MPESA name</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                $pairedShares = \App\Models\UserSharePair::where('paired_user_share_id', $share->id)->orderBy('id', 'desc')->get();
                            ?>

                            <?php $__currentLoopData = $pairedShares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $pairedShare): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $payment = \App\Models\UserSharePayment::where('user_share_pair_id', $pairedShare->id)->orderBy('id', 'desc')->first();
                                ?>
                                <tr>
                                    <td><?php echo e($key + 1); ?></td>
                                    <td><?php echo e($pairedShare->pairedUserShare->user->name); ?></td>
                                    <td><?php echo e($pairedShare->pairedUserShare->user->username); ?></td>
                                    <td><?php echo e(json_decode($pairedShare->pairedUserShare->user->business_profile)->mpesa_name); ?></td>
                                    <td><?php echo e($pairedShare->share); ?></td>
                                    <td>
                                        <?php if($payment && $payment->status === 'paid'): ?>
                                            <span class="badge bg-success">Paid, waiting for confirmation</span>
                                        <?php elseif($payment && $payment->status === 'conformed'): ?>
                                            <span class="badge bg-success">Payment confirmed</span>
                                        <?php elseif(\Carbon\Carbon::parse($pairedShare->created_at)->addHour(3) >= now()): ?>
                                            <span class="badge bg-primary">Waiting for payment</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Payment time expired</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Basic example"></a>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#soldShareDetails<?php echo e($pairedShare->id); ?>">
                                                Details
                                            </button>
                                            <?php if($payment): ?>
                                                <div class="modal fade" id="soldShareDetails<?php echo e($pairedShare->id); ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="staticBackdropLabel">Payment confirmation</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <table class="table table-bordered">
                                                                    <tbody>
                                                                        <tr>
                                                                            <th>Sender name</th>
                                                                            <td><?php echo e($payment->name); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Amount sent from</th>
                                                                            <td><?php echo e($payment->number); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Amount received from</th>
                                                                            <td><?php echo e($payment->number); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Amount</th>
                                                                            <td><?php echo e(formatPrice($payment->amount)); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Transaction no</th>
                                                                            <td><?php echo e($payment->txs_id); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Note by sender</th>
                                                                            <td><?php echo e($payment->note_by_sender); ?></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>


                                                                <?php if($payment && $payment->status === 'conformed'): ?>
                                                                    <div class="border border-dashed border-success p-3 my-3">
                                                                        <h3 class="text-center m-0 p-0">Payment completed. Thanks you</h3>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <form id="paymentApproveForm<?php echo e($payment->id); ?>" action="<?php echo e(route('share.paymentApprove')); ?>" method="post">
                                                                        <?php echo csrf_field(); ?>
                                                                        <div class="form-group">
                                                                            <label>Comment <small>if any</small></label>
                                                                            <textarea name="note_by_receiver" class="form-control"></textarea>
                                                                            <input type="hidden" value="<?php echo e($payment->id); ?>" name="paymentId">
                                                                        </div>
                                                                        <button type="button" onclick="handlePaymentConformSubmit(<?php echo e($payment->id); ?>)" class="btn btn-success mt-3 float-end">Confirm payment</button>
                                                                    </form>
                                                                <?php endif; ?>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal -->

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script>
        function handlePaymentConformSubmit(paymentId) {
            $('#paymentApproveForm'+paymentId).submit();

            
            
            
            
            
            
            
            
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/user-panel/share/sold-share-view.blade.php ENDPATH**/ ?>