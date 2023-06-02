<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="<?php echo e(URL::asset('assets/libs/bootstrap/bootstrap.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/libs/simplebar/simplebar.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/libs/node-waves/node-waves.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/libs/feather-icons/feather-icons.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/js/pages/plugins/lord-icon-2.1.0.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/js/plugins.min.js')); ?>"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script src="<?php echo e(URL::asset('assets/js/pages/datatables.init.js')); ?>"></script>

<script src="<?php echo e(URL::asset('/assets/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('/assets/js/pages/sweetalerts.init.js')); ?>"></script>

<script src="<?php echo e(URL::asset('/assets/js/app.min.js')); ?>"></script>
@toastr_js
@toastr_render
<script>
    <?php if(count($errors) > 0): ?>
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            toastr.error("<?php echo e($error); ?>");
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
</script>
<script>
    $(document).on("click", ".delete_two", function(e){
        e.preventDefault();

        var link = $(this).attr("href");

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'delete'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link;
            }
        })
    });
</script>
<?php echo $__env->yieldContent('script'); ?>
<?php echo $__env->yieldContent('script-bottom'); ?>

<?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/layouts/vendor-scripts.blade.php ENDPATH**/ ?>