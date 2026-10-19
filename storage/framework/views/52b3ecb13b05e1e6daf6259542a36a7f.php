<?php $__env->startSection('title', 'Workspace'); ?>

<?php $__env->startSection('page-title'); ?>
    <i class="fas fa-tasks"></i> My Workspace
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <!-- Personal Dashboard -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-user"></i> My Tasks for Today
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $myTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="border-bottom pb-2 mb-2">
                        <h6 class="mb-1"><?php echo e(Str::limit($task->title, 50)); ?></h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge <?php echo e($task->getPriorityBadgeClass()); ?>"><?php echo e(ucfirst($task->priority)); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->due_date): ?>
                                <small class="text-muted">Due: <?php echo e($task->due_date->format('M d')); ?></small>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->progress > 0): ?>
                            <div class="progress mt-2" style="height: 6px;">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo e($task->progress); ?>%"></div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-muted mb-0">No tasks assigned to you today.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- Overdue Tasks -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($overdueTasks->count() > 0): ?>
            <div class="card mb-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle"></i> Overdue Milestones
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $overdueTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="border-bottom pb-2 mb-2">
                            <h6 class="mb-1 text-danger"><?php echo e(Str::limit($task->title, 40)); ?></h6>
                            <small class="text-muted">Due: <?php echo e($task->due_date->format('M d, Y')); ?></small>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bell"></i> Recent Updates
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentComments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="border-bottom pb-2 mb-2">
                        <strong><?php echo e($comment->user->name); ?></strong> commented on:
                        <a href="<?php echo e(route('tasks.show', $comment->task)); ?>"><?php echo e(Str::limit($comment->task->title, 30)); ?></a>
                        <br><small class="text-muted"><?php echo e($comment->created_at->diffForHumans()); ?></small>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-muted mb-0">No recent updates.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pipeline View (Kanban Board) -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Task Pipeline (Kanban Board)</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- To Do Column -->
                    <div class="col-md-3">
                        <div class="card h-100 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">To Do</h6>
                            </div>
                            <div class="card-body p-0">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $myTasks->where('status', 'todo'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="p-3 border-bottom">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?php echo e(Str::limit($task->title, 30)); ?></h6>
                                            <span class="badge bg-light text-dark"><?php echo e($task->project->name ?? 'No Project'); ?></span>
                                        </div>
                                        <p class="text-muted small mb-1"><?php echo e(Str::limit($task->description, 60)); ?></p>
                                        <div class="mb-2">
                                            <small class="text-muted">Assigned by: <?php echo e($task->creator->name); ?></small>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->due_date): ?>
                                            <div class="mb-1">
                                                <small class="text-muted <?php echo e($task->isOverdue() ? 'text-danger' : ''); ?>>
                                                    Due: <?php echo e($task->due_date->format('M d, Y')); ?>

                                                    <?php if($task->isOverdue()): ?>
                                                        <i class="fas fa-exclamation-circle"></i>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </small>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo e($task->progress); ?>%"></div>
                                        </div>
                                        <div class="mt-2">
                                            <form action="<?php echo e(route('tasks.update-status', $task)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="todo" selected>To Do</option>
                                                    <option value="in_progress">In Progress</option>
                                                    <option value="blocked">Blocked</option>
                                                    <option value="completed">Completed</option>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($myTasks->where('status', 'todo')->isEmpty()): ?>
                                    <div class="p-3 text-center text-muted">
                                        <i class="fas fa-inbox mb-2"></i>
                                        <p class="mb-0">No tasks in To Do</p>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- In Progress Column -->
                    <div class="col-md-3">
                        <div class="card h-100 border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">In Progress</h6>
                            </div>
                            <div class="card-body p-0">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $myTasks->where('status', 'in_progress'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="p-3 border-bottom">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?php echo e(Str::limit($task->title, 30)); ?></h6>
                                            <span class="badge bg-light text-dark"><?php echo e($task->project->name ?? 'No Project'); ?></span>
                                        </div>
                                        <p class="text-muted small mb-1"><?php echo e(Str::limit($task->description, 60)); ?></p>
                                        <div class="mb-2">
                                            <small class="text-muted">Assigned by: <?php echo e($task->creator->name); ?></small>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->due_date): ?>
                                            <div class="mb-1">
                                                <small class="text-muted <?php echo e($task->isOverdue() ? 'text-danger' : ''); ?>>
                                                    Due: <?php echo e($task->due_date->format('M d, Y')); ?>

                                                    <?php if($task->isOverdue()): ?>
                                                        <i class="fas fa-exclamation-circle"></i>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </small>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo e($task->progress); ?>%"></div>
                                        </div>
                                        <div class="mt-2">
                                            <form action="<?php echo e(route('tasks.update-status', $task)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="todo">To Do</option>
                                                    <option value="in_progress" selected>In Progress</option>
                                                    <option value="blocked">Blocked</option>
                                                    <option value="completed">Completed</option>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($myTasks->where('status', 'in_progress')->isEmpty()): ?>
                                    <div class="p-3 text-center text-muted">
                                        <i class="fas fa-sync-alt mb-2"></i>
                                        <p class="mb-0">No tasks in Progress</p>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Blocked Column -->
                    <div class="col-md-3">
                        <div class="card h-100 border-warning">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0">Blocked</h6>
                            </div>
                            <div class="card-body p-0">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $myTasks->where('status', 'blocked'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="p-3 border-bottom>
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?php echo e(Str::limit($task->title, 30)); ?></h6>
                                            <span class="badge bg-light text-dark"><?php echo e($task->project->name ?? 'No Project'); ?></span>
                                        </div>
                                        <p class="text-muted small mb-1"><?php echo e(Str::limit($task->description, 60)); ?></p>
                                        <div class="mb-2">
                                            <small class="text-muted">Assigned by: <?php echo e($task->creator->name); ?></small>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->due_date): ?>
                                            <div class="mb-1">
                                                <small class="text-muted <?php echo e($task->isOverdue() ? 'text-danger' : ''); ?>>
                                                    Due: <?php echo e($task->due_date->format('M d, Y')); ?>

                                                    <?php if($task->isOverdue()): ?>
                                                        <i class="fas fa-exclamation-circle"></i>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </small>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo e($task->progress); ?>%"></div>
                                        </div>
                                        <div class="mt-2">
                                            <form action="<?php echo e(route('tasks.update-status', $task)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="todo">To Do</option>
                                                    <option value="in_progress">In Progress</option>
                                                    <option value="blocked" selected>Blocked</option>
                                                    <option value="completed">Completed</option>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($myTasks->where('status', 'blocked')->isEmpty()): ?>
                                    <div class="p-3 text-center text-muted">
                                        <i class="fas fa-ban mb-2"></i>
                                        <p class="mb-0">No blocked tasks</p>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Column -->
                    <div class="col-md-3">
                        <div class="card h-100 border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Completed</h6>
                            </div>
                            <div class="card-body p-0">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $createdTasks->where('status', 'completed'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="p-3 border-bottom">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?php echo e(Str::limit($task->title, 30)); ?></h6>
                                            <span class="bg-light text-dark"><?php echo e($task->project->name ?? 'No Project'); ?></span>
                                        </div>
                                        <p class="text-muted small mb-1"><?php echo e(Str::limit($task->description, 60)); ?></p>
                                        <div class="mb-2">
                                            <small class="text-muted">Assigned by: <?php echo e($task->creator->name); ?></small>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->due_date): ?>
                                            <div class="mb-1">
                                                <small class="text-muted">
                                                    Completed: <?php echo e($task->updated_at->format('M d, Y')); ?>

                                                </small>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                                        </div>
                                        <div class="mt-2 text-end">
                                            <small class="text-muted"><?php echo e($task->updated_at->diffForHumans()); ?> ago</small>
                                        </div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($createdTasks->where('status', 'completed')->isEmpty()): ?>
                                    <div class="p-3 text-center text-muted">
                                        <i class="fas fa-check-circle mb-2"></i>
                                        <p class="mb-0">No completed tasks</p>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Created Tasks Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">My Created Tasks</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $createdTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <h6 class="mb-1"><?php echo e(Str::limit($task->title, 40)); ?></h6>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->assignee): ?>
                                <small class="text-muted">Assigned to: <?php echo e($task->assignee->name); ?></small>
                            <?php else: ?>
                                <small class="text-muted">Unassigned</small>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="text-end">
                            <span class="badge <?php echo e($task->getStatusBadgeClass()); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $task->status))); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->progress > 0 && $task->status !== 'completed'): ?>
                                <div class="progress mt-1" style="width: 80px; height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo e($task->progress); ?>%"></div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-muted mb-0">You haven't created any tasks yet.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="card-footer text-end">
                <a href="<?php echo e(route('tasks.create')); ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create New Task
                </a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
           
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\tasks\workspace.blade.php ENDPATH**/ ?>