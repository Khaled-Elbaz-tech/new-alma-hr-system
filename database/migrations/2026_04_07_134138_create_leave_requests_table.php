<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days'); // Calculated days (excluding weekends/holidays)
            $table->integer('calendar_days'); // Actual calendar days
            $table->text('reason')->nullable();
            
            // Exit/Re-entry and Flight options
            $table->boolean('exit_reentry_required')->default(false);
            $table->boolean('flight_ticket_required')->default(false);
            
            // Approval workflow
            $table->enum('status', ['pending', 'hr_approved', 'manager_approved', 'approved', 'rejected', 'cancelled'])->default('pending');
            
            // HR Approval
            $table->foreignId('hr_approved_by')->nullable()->constrained('users');
            $table->timestamp('hr_approved_at')->nullable();
            $table->text('hr_comments')->nullable();
            
            // Manager Approval
            $table->foreignId('manager_approved_by')->nullable()->constrained('users');
            $table->timestamp('manager_approved_at')->nullable();
            $table->text('manager_comments')->nullable();
            
            // Final rejection (if any)
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Who created this request (for HR entering historical data)
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_historical')->default(false); // For data entry of past leaves
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
