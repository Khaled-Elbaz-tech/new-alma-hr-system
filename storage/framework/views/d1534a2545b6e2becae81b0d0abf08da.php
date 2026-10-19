<?php $__env->startSection('title', 'Create Field'); ?>
<?php $__env->startSection('page-title', 'Add Custom Field'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Field Configuration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('dynamic-forms.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Field Name (unique, lowercase with underscores)</label>
                            <input type="text" name="field_name" class="form-control" pattern="^[a-z_]+$" title="Lowercase letters and underscores only" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Field Label (display name)</label>
                            <input type="text" name="field_label" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Field Type</label>
                            <select name="field_type" class="form-select" required>
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="email">Email</option>
                                <option value="phone">Phone</option>
                                <option value="attachment">Attachment/File</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="radio">Radio</option>
                                <option value="select">Dropdown Select</option>
                                <option value="yes_no">Yes/No</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" name="field_section" class="form-control" value="general" placeholder="e.g., personal, job, emergency">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Options (JSON format for select/radio/checkbox)</label>
                        <textarea name="field_options" class="form-control" rows="2" placeholder='["Option 1", "Option 2", "Option 3"]'></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="field_order" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_required" class="form-check-input" id="is_required">
                            <label class="form-check-label" for="is_required">Required Field</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="employee_can_view" class="form-check-input" id="employee_can_view" checked>
                            <label class="form-check-label" for="employee_can_view">Employee Can View</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="employee_can_edit" class="form-check-input" id="employee_can_edit">
                            <label class="form-check-label" for="employee_can_edit">Employee Can Edit</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="hr_can_view" class="form-check-input" id="hr_can_view" checked>
                            <label class="form-check-label" for="hr_can_view">HR Can View</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="hr_can_edit" class="form-check-input" id="hr_can_edit" checked>
                            <label class="form-check-label" for="hr_can_edit">HR Can Edit</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Field</button>
                    <a href="<?php echo e(route('dynamic-forms.index')); ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\dynamic-forms\create.blade.php ENDPATH**/ ?>