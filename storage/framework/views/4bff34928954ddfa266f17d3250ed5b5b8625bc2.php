<?php $__env->startSection('title'); ?> <?php echo e($pageTitle); ?>  <?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> <?php echo app('translator')->get('translation.dashboard'); ?> <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> <?php echo e($pageTitle); ?> <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0"> <?php echo e($pageTitle); ?> </h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.role.permission.save', $role->id)); ?>" method="post">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>

                        <div class="role-permission-list">
                            <div class="module-wise-permission-single pt-3 pb-3 text-center">
                                <div class="module-name font-medium d-inline-block">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="select-all-permission" onchange="selectAllPermission(this)">
                                        <label class="form-check-label" for="select-all-permission">Select all permission</label>
                                    </div>

                                </div>
                            </div>

                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th scope="col">Module name</th>
                                    <th scope="col">Permissions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td style="width: 40%; vertical-align: middle">
                                            <div class="d-flex justify-content-start">
                                                <span>
                                                <?php echo e(ucfirst(str_replace('-', ' ', $permission['module_name']))); ?>

                                            </span>
                                                <div class="form-check form-switch mb-2 ms-2">
                                                    <input
                                                        class="form-check-input checkbox-module-name parent-<?php echo e($permission['module_name']); ?>"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="select-<?php echo e($permission['module_name']); ?>-module-permission"
                                                        <?php if(isAllPermissionOfModuleActive($permission['permission'], $allPermission)): echo 'checked'; endif; ?>
                                                        onchange="selectModuleWisePermission(this, '<?php echo e($permission['module_name']); ?>')"
                                                    >
                                                    <label class="form-check-label" for="select-<?php echo e($permission['module_name']); ?>-module-permission">Select All</label>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php $__currentLoopData = $permission['permission']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permissionItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="form-check form-switch mb-2">
                                                    <input
                                                        class="form-check-input permissions <?php echo e($permission['module_name']); ?>"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="check-permission-<?php echo e($permissionItem->name); ?>"
                                                        <?php if(in_array($permissionItem->name, $allPermission)): echo 'checked'; endif; ?>
                                                        name="<?php echo e($permissionItem->name); ?>"
                                                        onclick="handlePermissionCheck('<?php echo e($permission['module_name']); ?>', this)"
                                                    >
                                                    <label class="form-check-label" for="check-permission-<?php echo e($permissionItem->name); ?>">
                                                        <?php echo e(ucfirst(str_replace('-',' ', $permissionItem->name))); ?>

                                                    </label>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                            <div class="float-end" >
                                <button type="submit" class="btn btn-primary">
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        <!--end col-->
    </div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>
        function selectAllPermission(el) {
            const elements =  document.getElementsByClassName('permissions');
            const moduleElements = document.getElementsByClassName('checkbox-module-name');
            if(el.checked) {
                for (let i = 0; i < elements.length; ++i) { elements[i].checked = true; }
                for (let i = 0; i < moduleElements.length; ++i) { moduleElements[i].checked = true; }

            }else {
                for (let i = 0; i < elements.length; ++i) { elements[i].checked = false; }
                for (let i = 0; i < moduleElements.length; ++i) { moduleElements[i].checked = false; }
            }
        }

        function selectModuleWisePermission(el, moduleName) {
            const elements =  document.getElementsByClassName(moduleName);
            if(el.checked) {
                for (let i = 0; i < elements.length; ++i) { elements[i].checked = true; }
            }else {
                for (let i = 0; i < elements.length; ++i) { elements[i].checked = false; }
            }
        }


        function handlePermissionCheck(moduleName, el) {

            const elements =  document.getElementsByClassName(moduleName);
            let status = true;
            for (let i = 0; i < elements.length; ++i) {
                if(!elements[i].checked) {
                    status = false;
                }
            }
            const parentElement = document.getElementsByClassName(`parent-${moduleName}`)[0].checked = status;
        }

    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /opt/lampp/htdocs/other/autobidder/resources/views/admin-panel/roles/permission.blade.php ENDPATH**/ ?>