<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Aligns with Excel spec: one row per employee per year for annual leave
     */
    public function up(): void
    {
        // MySQL: the unique index also backs the employee_id foreign key,
        // so the FK needs its own index first or the drop is refused (error 1553).
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->index('employee_id');
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            // Drop the unique index first (required for SQLite)
            $table->dropUnique(['employee_id', 'leave_type_id', 'year']);
            
            // Remove leave_type_id (annual leave only per Excel)
            $table->dropForeign(['leave_type_id']);
            $table->dropColumn('leave_type_id');
            
            // Remove adjustment columns (not in Excel spec)
            if (Schema::hasColumn('leave_balances', 'carried_over')) {
                $table->dropColumn('carried_over');
            }
            if (Schema::hasColumn('leave_balances', 'adjustment')) {
                $table->dropColumn('adjustment');
            }
            if (Schema::hasColumn('leave_balances', 'adjustment_reason')) {
                $table->dropColumn('adjustment_reason');
            }
            
            // Add Excel-specified columns
            $table->integer('months_from_hiring')->default(0)->after('year')
                  ->comment('Months worked in this year (for proration)');
            $table->integer('ticket_count')->default(0)->after('months_from_hiring')
                  ->comment('Flight ticket count for this year');
            $table->integer('exit_reentry_count')->default(0)->after('ticket_count')
                  ->comment('Exit/re-entry count for this year');
            
            // Add new unique constraint: one row per employee per year
            $table->unique(['employee_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique(['employee_id', 'year']);
            
            // Remove Excel columns
            $table->dropColumn('months_from_hiring');
            $table->dropColumn('ticket_count');
            $table->dropColumn('exit_reentry_count');
            
            // Restore leave_type_id
            $table->foreignId('leave_type_id')->constrained('leave_types')->after('employee_id');
            
            // Restore adjustment columns
            $table->decimal('carried_over', 5, 2)->default(0)->after('used_days');
            $table->decimal('adjustment', 5, 2)->default(0)->after('carried_over');
            $table->text('adjustment_reason')->nullable()->after('adjustment');
            
            // Restore old unique constraint
            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });
    }
};
