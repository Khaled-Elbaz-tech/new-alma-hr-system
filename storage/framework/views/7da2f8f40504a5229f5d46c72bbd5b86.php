<?php $__env->startSection('title', 'Activity Logs'); ?>
<?php $__env->startSection('page-title', 'System Activity Logs'); ?>

<?php $__env->startSection('content'); ?>
<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($action); ?>" <?php echo e(request('action') == $action ? 'selected' : ''); ?>><?php echo e($action); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control" value="<?php echo e(request('start_date')); ?>" placeholder="Start Date">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control" value="<?php echo e(request('end_date')); ?>" placeholder="End Date">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="<?php echo e(route('activity-logs.index')); ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
            <div class="col-md-4 text-end">
                <span class="text-muted"><i class="fas fa-lock"></i> Read-only Audit Log</span>
            </div>
        </form>
    </div>
</div>

<!-- Log Categories Info -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="alert alert-light">
            <h6><i class="fas fa-info-circle"></i> Logged Actions</h6>
            <div class="row">
                <div class="col-md-3">
                    <strong>Authentication:</strong> login, logout
                </div>
                <div class="col-md-3">
                    <strong>CRUD:</strong> create, update, delete, activate, restore
                </div>
                <div class="col-md-3">
                    <strong>Leave:</strong> leave_request_create, leave_approve_hr, leave_approve_manager, leave_reject, leave_cancel
                </div>
                <div class="col-md-3">
                    <strong>System:</strong> update_weekend_settings, create_historical, etc.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Logs Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history"></i> All Activity Logs</h5>
    </div>
    <div class="card-body">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logs->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date/Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Model</th>
                            <th>IP Address</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td>
                                <small><?php echo e($log->created_at->format('d M Y')); ?></small><br>
                                <span class="text-muted"><?php echo e($log->created_at->format('H:i:s')); ?></span>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->user): ?>
                                    <strong><?php echo e($log->user->name); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo e($log->user->email); ?></small>
                                <?php else: ?>
                                    <span class="text-danger">System</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $badgeColor = 'secondary';
                                    if(str_contains($log->action, 'login') || str_contains($log->action, 'logout')) $badgeColor = 'info';
                                    elseif(str_contains($log->action, 'create')) $badgeColor = 'success';
                                    elseif(str_contains($log->action, 'update') || str_contains($log->action, 'approve')) $badgeColor = 'primary';
                                    elseif(str_contains($log->action, 'delete') || str_contains($log->action, 'reject')) $badgeColor = 'danger';
                                    elseif(str_contains($log->action, 'activate')) $badgeColor = 'warning';
                                ?>
                                <span class="badge bg-<?php echo e($badgeColor); ?>"><?php echo e($log->action); ?></span>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->model_type): ?>
                                    <span class="badge bg-light text-dark"><?php echo e(class_basename($log->model_type)); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->model_id): ?>
                                        <small class="text-muted">#<?php echo e($log->model_id); ?></small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo e($log->ip_address ?? 'N/A'); ?></small>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->new_values || $log->old_values): ?>
                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#logModal<?php echo e($log->id); ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    
                                    <div class="modal fade" id="logModal<?php echo e($log->id); ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Activity Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>User:</strong> <?php echo e($log->user->name ?? 'System'); ?></p>
                                                    <p><strong>Action:</strong> <?php echo e($log->action); ?></p>
                                                    <p><strong>IP:</strong> <?php echo e($log->ip_address ?? 'N/A'); ?></p>
                                                    <p><strong>Time:</strong> <?php echo e($log->created_at); ?></p>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->old_values): ?>
                                                    <p><strong>Old Values:</strong></p>
                                                    <pre class="bg-light p-2"><?php echo e(json_encode($log->old_values, JSON_PRETTY_PRINT)); ?></pre>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->new_values): ?>
                                                    <p><strong>New Values:</strong></p>
                                                    <pre class="bg-light p-2"><?php echo e(json_encode($log->new_values, JSON_PRETTY_PRINT)); ?></pre>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($logs->links()); ?>

        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No activity logs found.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\activity-logs\index.blade.php ENDPATH**/ ?>