<?php $__env->startSection('title', 'Change Password'); ?>
<?php $__env->startSection('page-title', 'Change Password'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Change Your Password</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                    <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <form method="POST" action="<?php echo e(route('password.change')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-lock"></i> Current Password</label>
                        <div class="input-group">
                            <input type="password" name="current_password" class="form-control" required>
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-plus-circle"></i> New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password" class="form-control" 
                                   required minlength="8" 
                                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$"
                                   title="Must contain: uppercase, lowercase, number, special character (@$!%*?&)">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <small class="text-muted">Minimum 8 characters. Must include: uppercase, lowercase, number, special character (@$!%*?&)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-check-circle"></i> Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\auth\change-password.blade.php ENDPATH**/ ?>