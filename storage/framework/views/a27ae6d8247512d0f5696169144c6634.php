<?php $__env->startSection('title', 'Edit Field'); ?>
<?php $__env->startSection('page-title', 'Edit Custom Field'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Field: <?php echo e($employeeField->field_label); ?></h5>
            </div>
            <div class="card-body">
                  <form method="POST" action="<?php echo e(route('dynamic-forms.update', $employeeField->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <!-- DEBUG: Form action uses explicit parameter mapping -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Field Label</label>
                            <input type="text" name="field_label" class="form-control" value="<?php echo e($employeeField->field_label); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" name="field_section" class="form-control" value="<?php echo e($employeeField->field_section); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Options (JSON format)</label>
                        <textarea name="field_options" class="form-control" rows="2"><?php echo e($employeeField->field_options ? json_encode($employeeField->field_options) : ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="field_order" class="form-control" value="<?php echo e($employeeField->field_order); ?>">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_required" class="form-check-input" id="is_required" <?php echo e($employeeField->is_required ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_required">Required Field</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="employee_can_view" class="form-check-input" id="employee_can_view" <?php echo e($employeeField->employee_can_view ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="employee_can_view">Employee Can View</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="employee_can_edit" class="form-check-input" id="employee_can_edit" <?php echo e($employeeField->employee_can_edit ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="employee_can_edit">Employee Can Edit</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="hr_can_view" class="form-check-input" id="hr_can_view" <?php echo e($employeeField->hr_can_view ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="hr_can_view">HR Can View</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="hr_can_edit" class="form-check-input" id="hr_can_edit" <?php echo e($employeeField->hr_can_edit ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="hr_can_edit">HR Can Edit</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" <?php echo e($employeeField->is_active ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Field</button>
                    <a href="<?php echo e(route('dynamic-forms.index')); ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\dynamic-forms\edit.blade.php ENDPATH**/ ?>