<?php $__env->startSection('title'); ?> <?php echo e($pageTitle); ?>  <?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> <?php echo app('translator')->get('translation.dashboard'); ?> <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> <?php echo e($pageTitle); ?> <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <?php if($share->get_from === 'purchase'): ?>
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
                                    <th>Seller name</th>
                                    <th>Seller username</th>
                                    <th>Seller M-pesa name</th>
                                    <th>Seller M-pesa number</th>
                                    <th>Paired share quantity</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $__currentLoopData = $share->pairedShares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $pairedShare): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $payment = \App\Models\UserSharePayment::where('user_share_pair_id', $pairedShare->id)->orderBy('id', 'desc')->exists();
                                ?>
                                <tr>
                                    <td><?php echo e($key + 1); ?></td>
                                    <td><?php echo e($pairedShare->pairedShare->user->name); ?></td>
                                    <td><?php echo e($pairedShare->pairedShare->user->username); ?></td>
                                    <td><?php echo e(json_decode($pairedShare->pairedShare->user->business_profile)->mpesa_name); ?></td>
                                    <td><?php echo e(json_decode($pairedShare->pairedShare->user->business_profile)->mpesa_no); ?></td>
                                    <td><?php echo e($pairedShare->share); ?></td>
                                    <td>
                                        <?php if($pairedShare->is_paid): ?>
                                            <span class="badge bg-success">Paid and confirmed</span>
                                        <?php elseif($payment): ?>
                                            <span class="badge bg-info">Paid, waiting for confirmation</span>
                                        <?php elseif(\Carbon\Carbon::parse($pairedShare->created_at)->addHour(3) >= now()): ?>
                                            <span class="badge bg-primary">Waiting for payment</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Payment time expired</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($pairedShare->is_paid): ?>
                                            <span class="badge bg-success">Paid and confirmed</span>
                                        <?php elseif($payment): ?>
                                            <span class="badge bg-info">Paid, waiting for confirmation</span>
                                        <?php elseif(\Carbon\Carbon::parse($pairedShare->created_at)->addHour(3) >= now()): ?>
                                            <div class="btn-group" role="group" aria-label="Basic example"></a>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo e($pairedShare->id); ?>">
                                                    Pay now
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Payment time expired</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Modal -->
                                <div class="modal fade" id="paymentModal<?php echo e($pairedShare->id); ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="staticBackdropLabel">Payment submit form</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="<?php echo e(route('share.payment')); ?>" method="post">
                                                <?php echo csrf_field(); ?>
                                                <div class="modal-body">

                                                    <div class="payment-details mb-4">
                                                        <h5>
                                                            You are buying <b><?php echo e($pairedShare->share); ?> shares </b> from the MR/MS <b><?php echo e($pairedShare->pairedShare->user->name); ?></b>.
                                                            Each share cost  <?php echo e(formatPrice($pairedShare->userShare->trade->price)); ?></b>.
                                                        </h5>
                                                        <h5>So you have to pay <?php echo e($pairedShare->share); ?> X <?php echo e($pairedShare->userShare->trade->price); ?> = <?php echo e(formatPrice($pairedShare->share * $pairedShare->userShare->trade->price)); ?></h5>
                                                        <h5>
                                                            <b>
                                                                <i>Please pay the amount in this <q><?php echo e(json_decode($pairedShare->pairedShare->user->business_profile)->mpesa_no); ?></q> and submit the form.</i>
                                                            </b>
                                                        </h5>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Your name</label>
                                                        <input type="text" class="form-control bg-light" name="name" value="<?php echo e(json_decode(auth()->user()->business_profile)->mpesa_name); ?>" readonly>
                                                        <?php $__errorArgs = ['name'];
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
                                                    </div>

                                                    <input type="hidden" value="<?php echo e($share->id); ?>" name="user_share_id">
                                                    <input type="hidden" value="<?php echo e($pairedShare->id); ?>" name="user_share_pair_id">
                                                    <input type="hidden" value="<?php echo e($pairedShare->pairedShare->user->id); ?>" name="receiver_id">
                                                    <input type="hidden" value="<?php echo e(auth()->user()->id); ?>" name="sender_id">
                                                    <input type="hidden" value="<?php echo e(json_decode($pairedShare->pairedShare->user->business_profile)->mpesa_no); ?>" name="received_phone_no">
                                                    <div class="mb-3">
                                                        <label class="form-label">Phone no <small>(The number you sent the money from)</small></label>
                                                        <input type="text" class="form-control bg-light" name="number" value="<?php echo e(json_decode(auth()->user()->business_profile)->mpesa_no); ?>" readonly>
                                                        <?php $__errorArgs = ['number'];
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
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Payment transaction id</label>
                                                        <input type="text" class="form-control" name="txs_id" value="<?php echo e(old('txs_id')); ?>" required>
                                                        <?php $__errorArgs = ['txs_id'];
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
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Amount</label>
                                                        <input type="number" class="form-control bg-light" name="amount" value="<?php echo e($pairedShare->share * $pairedShare->userShare->trade->price); ?>" readonly>
                                                        <?php $__errorArgs = ['amount'];
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
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Note</label>
                                                        <textarea class="form-control" name="note_by_sender"><?php echo e(old('note_by_sender')); ?></textarea>
                                                        <?php $__errorArgs = ['note_by_sender'];
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
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment history</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>SI</th>
                                    <th>Username</th>
                                    <th>Seller M-pesa no</th>
                                    <th>Sender phone no</th>
                                    <th>Sent amount</th>
                                    <th>status</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php $__currentLoopData = $share->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($key + 1); ?></td>
                                    <td><?php echo e($payment->receiver->username); ?></td>
                                    <td><?php echo e($payment->received_phone_no); ?></td>
                                    <td><?php echo e($payment->number); ?></td>
                                    <td><?php echo e($payment->amount); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo e($payment->status == 'conformed' ? 'Confirmed' : $payment->status); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <h2 class="text-info">No History, As the share is <?php echo e(str_replace('-', ' ', $share->get_from)); ?> itself.</h2>
                    <h4 class="mt-3">If you want to learn more about it. Please contact us from
                        <a href="<?php echo e(route('users.support')); ?>" class="text-info text-decoration-underline">here</a>
                    </h4>
                    <a href="<?php echo e(route('users.bought_shares')); ?>" class="btn btn-primary mt-3">Return back</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/user-panel/share/bought-share-view.blade.php ENDPATH**/ ?>