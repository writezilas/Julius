<div class="row">
    <?php
        $trades = \App\Models\Trade::where('status', 1)->OrderBy('id', 'desc')->get();
    ?>
    <?php $__currentLoopData = $trades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $trade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0 flex-grow-1"><?php echo e($trade->name); ?></h4>
                    <h6 class="mt-1 mb-0"><?php echo e(checkAvailableSharePerTrade($trade->id)); ?> Share available</h6>
                </div>
                <div class="card-body p-0">
                    <div class="p-3">
                        <form action="<?php echo e(route('user.bid')); ?>" method="post">
                            <?php echo csrf_field(); ?>
                            <div>
                                <div class="input-group mb-3">
                                    <label class="input-group-text">Amount</label>
                                    <input type="number" class="form-control" placeholder="0000" name="amount">
                                    <input type="hidden" value="<?php echo e($trade->id); ?>" name="trade_id">
                                </div>
                                <div class="input-group mb-0">
                                    <label class="input-group-text">Period</label>
                                    <?php
                                        $periods = \App\Models\TradePeriod::where('status', 1)->orderBy('days', 'asc')->get();
                                    ?>
                                    <select class="form-select" name="period">
                                        <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($period->days); ?>"><?php echo e($period->days); ?> days</option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3 pt-2">
                                <button type="submit" class="btn btn-primary w-100">Bid</button>
                            </div>
                        </form>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/components/trades.blade.php ENDPATH**/ ?>