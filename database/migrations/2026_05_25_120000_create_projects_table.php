<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->enum('status', ['active', 'completed', 'on_hold', 'cancelled'])->default('active');
                $table->timestamps();
            });
        }

        // The tasks table was removed with the task-management module.
        // Only add the link column on legacy installs where tasks still exists.
        if (Schema::hasTable('tasks') && ! Schema::hasColumn('tasks', 'project_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->after('department_id')
                      ->constrained()->onDelete('set null');
            });
        }

        if (! Schema::hasTable('project_user')) {
            Schema::create('project_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['project_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_user');
        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'project_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            });
        }
        Schema::dropIfExists('projects');
    }
};
