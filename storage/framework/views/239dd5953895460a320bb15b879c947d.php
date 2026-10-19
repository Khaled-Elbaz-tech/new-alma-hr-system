<?php $__env->startSection('title', 'Task Details'); ?>

<?php $__env->startSection('page-title'); ?>
    <i class="fas fa-tasks"></i> Task Details
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo e($task->title); ?></h5>
                <div>
                    <span class="badge <?php echo e($task->getPriorityBadgeClass()); ?> me-2"><?php echo e(ucfirst($task->priority)); ?></span>
                    <span class="badge <?php echo e($task->getStatusBadgeClass()); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $task->status))); ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->description): ?>
                    <div class="mb-4">
                        <h6>Description</h6>
                        <p class="text-muted"><?php echo e($task->description); ?></p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Details</h6>
                        <ul class="list-unstyled">
                            <li><strong>Created by:</strong> <?php echo e($task->creator->name); ?></li>
                            <li><strong>Assigned to:</strong> <?php echo e($task->assignee->name ?? 'Unassigned'); ?></li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->department): ?>
                                <li><strong>Department:</strong> <?php echo e($task->department->name); ?></li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->project): ?>
                                <li><strong>Project:</strong> <?php echo e($task->project->name); ?></li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->category): ?>
                                <li><strong>Category:</strong> <?php echo e($task->category); ?></li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Dates</h6>
                        <ul class="list-unstyled">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->start_date): ?>
                                <li><strong>Start Date:</strong> <?php echo e($task->start_date->format('M d, Y')); ?></li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->due_date): ?>
                                <li><strong>Due Date:</strong> 
                                    <span class="<?php echo e($task->isOverdue() ? 'text-danger' : ''); ?>">
                                        <?php echo e($task->due_date->format('M d, Y')); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->isOverdue()): ?>
                                            <i class="fas fa-exclamation-circle" title="Overdue"></i>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                </li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <li><strong>Created:</strong> <?php echo e($task->created_at->format('M d, Y')); ?></li>
                        </ul>
                    </div>
                </div>

                <div class="mb-4">
                    <h6>Progress</h6>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo e($task->progress); ?>%">
                            <?php echo e($task->progress); ?>%
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="<?php echo e(route('tasks.index')); ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <div>
                    <a href="<?php echo e(route('tasks.edit', $task)); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="<?php echo e(route('tasks.destroy', $task)); ?>" method="POST" class="d-inline" 
                          onsubmit="return confirm('Are you sure you want to delete this task?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Subtasks -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Subtasks</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->subtasks->count() > 0): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $task->subtasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subtask): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="d-flex align-items-center border-bottom py-2">
                            <form action="<?php echo e(route('tasks.subtasks.update', $subtask)); ?>" method="POST" class="d-flex align-items-center w-100">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <input type="checkbox" name="is_completed" value="1" 
                                       <?php echo e($subtask->is_completed ? 'checked' : ''); ?> 
                                       onchange="this.form.submit()" class="me-3">
                                <span class="<?php echo e($subtask->is_completed ? 'text-decoration-line-through text-muted' : ''); ?>">
                                    <?php echo e($subtask->title); ?>

                                </span>
                            </form>
                            <form action="<?php echo e(route('tasks.subtasks.destroy', $subtask)); ?>" method="POST" class="d-inline"
                                  onsubmit="return confirm('Remove this subtask?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-link text-danger">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">No subtasks yet.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <hr>
                <form action="<?php echo e(route('tasks.subtasks.store', $task)); ?>" method="POST" class="d-flex">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="title" class="form-control me-2" placeholder="Add new subtask..." required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Comments -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Comments & Updates</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->comments->count() > 0): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $task->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo e($comment->user->name); ?></strong>
                                <small class="text-muted"><?php echo e($comment->created_at->diffForHumans()); ?></small>
                            </div>
                            <p class="mb-1"><?php echo e($comment->content); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comment->hasAttachment()): ?>
                                <a href="<?php echo e(asset('storage/' . $comment->attachment_path)); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-paperclip"></i> <?php echo e($comment->attachment_name); ?>

                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">No comments yet.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <hr>
                <form action="<?php echo e(route('tasks.comments.store', $task)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <textarea name="content" class="form-control" rows="3" placeholder="Add a comment..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="attachment" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-comment"></i> Post Comment
                    </button>
                </form
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body>
                <form action="<?php echo e(route('tasks.update-status', $task)); ?>" method="POST" class="mb-3">
                    <?php echo csrf_field(); ?>
                    <label class="form-label">Change Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="todo" <?php echo e($task->status == 'todo' ? 'selected' : ''); ?>>To Do</option>
                        <option value="in_progress" <?php echo e($task->status == 'in_progress' ? 'selected' : ''); ?>>In Progress</option>
                        <option value="blocked" <?php echo e($task->status == 'blocked' ? 'selected' : ''); ?>>Blocked</option>
                        <option value="completed" <?php echo e($task->status == 'completed' ? 'selected' : ''); ?>>Completed</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo e($task->title); ?></h5>
                <div>
                    <span class="badge <?php echo e($task->getPriorityBadgeClass()); ?> me-2"><?php echo e(ucfirst($task->priority)); ?></span>
                    <span class="badge <?php echo e($task->getStatusBadgeClass()); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $task->status))); ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->description): ?>
                    <div class="mb-4">
                        <h6>Description</h6>
                        <p class="text-muted"><?php echo e($task->description); ?></p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Details</h6>
                        <ul class="list-unstyled">
                            <li><strong>Created by:</strong> <?php echo e($task->creator->name); ?></li>
                            <li><strong>Assigned to:</strong> <?php echo e($task->assignee->name ?? 'Unassigned'); ?></li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->department): ?>
                                <li><strong>Department:</strong> <?php echo e($task->department->name); ?></li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->category): ?>
                                <li><strong>Category:</strong> <?php echo e($task->category); ?></li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Dates</h6>
                        <ul class="list-unstyled">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->start_date): ?>
                                <li><strong>Start Date:</strong> <?php echo e($task->start_date->format('M d, Y')); ?></li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->due_date): ?>
                                <li><strong>Due Date:</strong> 
                                    <span class="<?php echo e($task->isOverdue() ? 'text-danger' : ''); ?>">
                                        <?php echo e($task->due_date->format('M d, Y')); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->isOverdue()): ?>
                                            <i class="fas fa-exclamation-circle" title="Overdue"></i>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                </li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <li><strong>Created:</strong> <?php echo e($task->created_at->format('M d, Y')); ?></li>
                        </ul>
                    </div>
                </div>

                <div class="mb-4">
                    <h6>Progress</h6>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo e($task->progress); ?>%">
                            <?php echo e($task->progress); ?>%
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="<?php echo e(route('tasks.index')); ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <div>
                    <a href="<?php echo e(route('tasks.edit', $task)); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="<?php echo e(route('tasks.destroy', $task)); ?>" method="POST" class="d-inline" 
                          onsubmit="return confirm('Are you sure you want to delete this task?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Subtasks -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Subtasks</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->subtasks->count() > 0): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $task->subtasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subtask): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="d-flex align-items-center border-bottom py-2">
                            <form action="<?php echo e(route('tasks.subtasks.update', $subtask)); ?>" method="POST" class="d-flex align-items-center w-100">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <input type="checkbox" name="is_completed" value="1" 
                                       <?php echo e($subtask->is_completed ? 'checked' : ''); ?> 
                                       onchange="this.form.submit()" class="me-3">
                                <span class="<?php echo e($subtask->is_completed ? 'text-decoration-line-through text-muted' : ''); ?>">
                                    <?php echo e($subtask->title); ?>

                                </span>
                            </form>
                            <form action="<?php echo e(route('tasks.subtasks.destroy', $subtask)); ?>" method="POST" class="d-inline"
                                  onsubmit="return confirm('Remove this subtask?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-link text-danger">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">No subtasks yet.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <hr>
                <form action="<?php echo e(route('tasks.subtasks.store', $task)); ?>" method="POST" class="d-flex">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="title" class="form-control me-2" placeholder="Add new subtask..." required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Comments -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Comments & Updates</h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->comments->count() > 0): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $task->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo e($comment->user->name); ?></strong>
                                <small class="text-muted"><?php echo e($comment->created_at->diffForHumans()); ?></small>
                            </div>
                            <p class="mb-1"><?php echo e($comment->content); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comment->hasAttachment()): ?>
                                <a href="<?php echo e(asset('storage/' . $comment->attachment_path)); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-paperclip"></i> <?php echo e($comment->attachment_name); ?>

                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">No comments yet.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <hr>
                <form action="<?php echo e(route('tasks.comments.store', $task)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <textarea name="content" class="form-control" rows="3" placeholder="Add a comment..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="attachment" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-comment"></i> Post Comment
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo e(route('tasks.update-status', $task)); ?>" method="POST" class="mb-3">
                    <?php echo csrf_field(); ?>
                    <label class="form-label">Change Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="todo" <?php echo e($task->status == 'todo' ? 'selected' : ''); ?>>To Do</option>
                        <option value="in_progress" <?php echo e($task->status == 'in_progress' ? 'selected' : ''); ?>>In Progress</option>
                        <option value="blocked" <?php echo e($task->status == 'blocked' ? 'selected' : ''); ?>>Blocked</option>
                        <option value="completed" <?php echo e($task->status == 'completed' ? 'selected' : ''); ?>>Completed</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp1\htdocs\hr-system\resources\views\tasks\show.blade.php ENDPATH**/ ?>