<?php $__env->startSection('title', 'Dynamic Form Fields'); ?>
<?php $__env->startSection('page-title', 'Dynamic Form Fields'); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-3">
    <div class="col-md-12">
        <a href="<?php echo e(route('dynamic-forms.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Field
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Custom Employee Fields</h5>
    </div>
    <div class="card-body">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fields->count() > 0): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $fieldsBySection; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section => $sectionFields): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted mb-3"><?php echo e(ucfirst($section)); ?></h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Field Label</th>
                                <th>Field Name</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Employee Can Edit</th>
                                <th>HR Can Edit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sectionFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><?php echo e($field->field_label); ?></td>
                                    <td><code><?php echo e($field->field_name); ?></code></td>
                                    <td><?php echo e(ucfirst($field->field_type)); ?></td>
                                    <td><?php echo e($field->is_required ? 'Yes' : 'No'); ?></td>
                                    <td><?php echo e($field->employee_can_edit ? 'Yes' : 'No'); ?></td>
                                    <td><?php echo e($field->hr_can_edit ? 'Yes' : 'No'); ?></td>
                                     <td>
                                         <a href="<?php echo e(route('dynamic-forms.edit', $field->id)); ?>" class="btn btn-sm btn-warning">Edit</a>
                                           <form action="<?php echo e(route('dynamic-forms.destroy', $field->id)); ?>" method="POST" class="d-inline">
                                             <?php echo csrf_field(); ?>
                                             <?php echo method_field('DELETE'); ?>
                                             <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this field?')">Delete</button>
                                         </form>
                                     </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <?php else: ?>
            <p class="text-muted">No custom fields defined yet.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\dynamic-forms\index.blade.php ENDPATH**/ ?>