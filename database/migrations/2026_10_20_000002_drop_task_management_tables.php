<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop ONLY the task-management tables.
     * No other table or data is touched.
     */
    public function up(): void
    {
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_subtasks');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('user_task_access');
    }

    public function down(): void
    {
        // Task management was removed permanently; nothing to restore.
    }
};
