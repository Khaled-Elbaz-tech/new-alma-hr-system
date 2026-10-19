<?php $__env->startSection('title', 'Employee Custom Fields'); ?>
<?php $__env->startSection('page-title', 'Custom Fields: ' . $employee->user->name); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12 mb-3">
        <a href="<?php echo e(route('employees.show', $employee)); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Employee
        </a>
    </div>
</div>

<?php
    $isHrOrSuperAdmin = auth()->user()->isHrManager() || auth()->user()->isSuperAdmin();
?>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            DEBUG: HR or Super Admin? <?php echo e($isHrOrSuperAdmin ? 'Yes' : 'No'); ?>. Fields count: <?php echo e($fields->count()); ?>

        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Custom Field Values</h5>
    </div>
    <div class="card-body">
        <?php
            $isHrOrSuperAdmin = auth()->user()->isHrManager() || auth()->user()->isSuperAdmin();
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isHrOrSuperAdmin): ?>
            <!-- Editable form for HR/Super Admin -->
            <form method="POST" action="<?php echo e(route('dynamic-forms.employee-fields.update', $employee)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fields->count() > 0): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section => $sectionFields): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted mb-3 border-bottom pb-2"><?php echo e(ucfirst($section)); ?></h6>
                            <div class="row">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sectionFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <?php echo e($field->field_label); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field->is_required): ?><span class="text-danger">*</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </label>
                                        
                                        <?php
                                            $fieldValue = $employee->fieldValues->where('field_id', $field->id)->first();
                                            $currentValue = $fieldValue ? $fieldValue->value : null;
                                            $options = is_array($field->field_options) ? $field->field_options : [];
                                        ?>
                                        
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($field->field_type):
                                            case ('text'): ?>
                                                <input type="text" name="field_<?php echo e($field->id); ?>" class="form-control" 
                                                       value="<?php echo e($currentValue); ?>" 
                                                       <?php echo e($field->is_required ? 'required' : ''); ?>>
                                                <?php break; ?>
                                            
                                            <?php case ('textarea'): ?>
                                                <textarea name="field_<?php echo e($field->id); ?>" class="form-control" rows="3"
                                                       <?php echo e($field->is_required ? 'required' : ''); ?>><?php echo e($currentValue); ?></textarea>
                                                <?php break; ?>
                                            
                                            <?php case ('number'): ?>
                                                <input type="number" name="field_<?php echo e($field->id); ?>" class="form-control" 
                                                       value="<?php echo e($currentValue); ?>" 
                                                       <?php echo e($field->is_required ? 'required' : ''); ?>>
                                                <?php break; ?>
                                            
                                            <?php case ('date'): ?>
                                                <input type="date" name="field_<?php echo e($field->id); ?>" class="form-control" 
                                                       value="<?php echo e($currentValue); ?>" 
                                                       <?php echo e($field->is_required ? 'required' : ''); ?>>
                                                <?php break; ?>
                                            
                                            <?php case ('email'): ?>
                                                <input type="email" name="field_<?php echo e($field->id); ?>" class="form-control" 
                                                       value="<?php echo e($currentValue); ?>" 
                                                       <?php echo e($field->is_required ? 'required' : ''); ?>>
                                                <?php break; ?>
                                            
                                            <?php case ('phone'): ?>
                                                <input type="tel" name="field_<?php echo e($field->id); ?>" class="form-control" 
                                                       value="<?php echo e($currentValue); ?>" 
                                                       <?php echo e($field->is_required ? 'required' : ''); ?>>
                                                <?php break; ?>
                                            
                                            <?php case ('yes_no'): ?>
                                                <select name="field_<?php echo e($field->id); ?>" class="form-select" <?php echo e($field->is_required ? 'required' : ''); ?>>
                                                    <option value="">Select...</option>
                                                    <option value="Yes" <?php echo e($currentValue == 'Yes' ? 'selected' : ''); ?>>Yes</option>
                                                    <option value="No" <?php echo e($currentValue == 'No' ? 'selected' : ''); ?>>No</option>
                                                </select>
                                                <?php break; ?>
                                            
                                            <?php case ('checkbox'): ?>
                                                <div class="form-check">
                                                    <input type="checkbox" name="field_<?php echo e($field->id); ?>" class="form-check-input" 
                                                           id="field_<?php echo e($field->id); ?>" value="1" <?php echo e($currentValue ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="field_<?php echo e($field->id); ?>">Yes</label>
                                                </div>
                                                <?php break; ?>
                                            
                                            <?php case ('select'): ?>
                                                <select name="field_<?php echo e($field->id); ?>" class="form-select" <?php echo e($field->is_required ? 'required' : ''); ?>>
                                                    <option value="">Select...</option>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                        <option value="<?php echo e($option); ?>" <?php echo e($currentValue == $option ? 'selected' : ''); ?>>
                                                            <?php echo e($option); ?>

                                                        </option>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                </select>
                                                <?php break; ?>
                                            
                                            <?php case ('radio'): ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <div class="form-check">
                                                        <input type="radio" name="field_<?php echo e($field->id); ?>" class="form-check-input" 
                                                               id="field_<?php echo e($field->id); ?>_<?php echo e($loop->index); ?>" value="<?php echo e($option); ?>" 
                                                               <?php echo e($currentValue == $option ? 'checked' : ''); ?>

                                                               <?php echo e($field->is_required ? 'required' : ''); ?>>
                                                        <label class="form-check-label" for="field_<?php echo e($field->id); ?>_<?php echo e($loop->index); ?>">
                                                            <?php echo e($option); ?>

                                                        </label>
                                                    </div>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <?php break; ?>
                                            
                                            <?php case ('attachment'): ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fieldValue && $fieldValue->attachment_path): ?>
                                                    <div class="mb-2">
                                                        <a href="<?php echo e(route('dynamic-forms.attachment.download', $fieldValue)); ?>" target="_blank" class="btn btn-sm btn-info">
                                                            <i class="fas fa-file"></i> View Current File
                                                        </a>
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <input type="file" name="field_<?php echo e($field->id); ?>" class="form-control" 
                                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field->is_required && !$currentValue): ?>
                                                    <small class="text-muted">Required</small>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php break; ?>
                                            
                                            <?php default: ?>
                                                <input type="text" name="field_<?php echo e($field->id); ?>" class="form-control" 
                                                       value="<?php echo e($currentValue); ?>" 
                                                       <?php echo e($field->is_required ? 'required' : ''); ?>>
                                        <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                <?php else: ?>
                    <p class="text-muted">No custom fields have been configured yet.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </form>
        <?php else: ?>
            <!-- Read-only view for employees -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fields->count() > 0): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section => $sectionFields): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted mb-3 border-bottom pb-2"><?php echo e(ucfirst($section)); ?></h6>
                        <div class="row">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sectionFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>
                                            <strong><?php echo e($field->field_label); ?></strong>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field->is_required): ?><span class="text-danger">*</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                        <span>
                                            <?php
                                                $fieldValue = $employee->fieldValues->where('field_id', $field->id)->first();
                                                $currentValue = $fieldValue ? $fieldValue->value : null;
                                            ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fieldValue && ($fieldValue->attachment_path || $currentValue)): ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($field->field_type):
                                                    case ('attachment'): ?>
                                                        <a href="<?php echo e(route('dynamic-forms.attachment.download', $fieldValue)); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-file-download"></i> Download
                                                        </a>
                                                    <?php break; ?>
                                                    <?php default: ?>
                                                        <span class="text-break"><?php echo e($currentValue); ?></span>
                                                    <?php break; ?>
                                                <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">No file uploaded</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php else: ?>
                <p class="text-muted">No custom fields have been configured yet.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\dynamic-forms\employee-fields.blade.php ENDPATH**/ ?>