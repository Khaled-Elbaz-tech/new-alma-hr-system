<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds balance column and foreign key to leave_years
     */
    public function up(): void
    {
        // MySQL: a foreign key needs identical column types on both sides.
        // leave_balances.year is YEAR, leave_years.year is INT — align them first.
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->integer('year')->change();
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            // Add balance column (stored, can be negative)
            if (! Schema::hasColumn('leave_balances', 'balance')) {
                $table->decimal('balance', 10, 2)->default(0)->after('exit_reentry_count')
                      ->comment('Remaining balance: entitled - used (can be negative)');
            }
            
            // Add foreign key to leave_years table
            $table->foreign('year')
                  ->references('year')
                  ->on('leave_years')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->dropForeign(['year']);
            $table->dropColumn('balance');
        });
    }
};
