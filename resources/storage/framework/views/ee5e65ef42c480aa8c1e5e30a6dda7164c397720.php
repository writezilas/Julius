<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <script>document.write(new Date().getFullYear())</script> © <?php echo e(env('APP_NAME', 'AUTO BIDDER')); ?>.
            </div>
            <div class="col-sm-6">
                <div class="text-sm-end d-none d-sm-block">
                    <a href="<?php echo e(route('page.privacy_policy')); ?>" class="me-2">Privacy policy</a>
                    <a href="<?php echo e(route('page.termsAndConditions')); ?>" class="me-2">Terms and condition</a>
                    <a href="<?php echo e(route('page.confidentialityPolicy')); ?>">Confidentiality Policy</a>
                </div>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/layouts/footer.blade.php ENDPATH**/ ?>