<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Leave Years table - Admin controls which years are active for leave tracking
     */
    public function up(): void
    {
        Schema::create('leave_years', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique()->comment('Year e.g. 2023, 2024');
            $table->boolean('is_active')->default(true)->comment('Admin can enable/disable year');
            $table->text('description')->nullable()->comment('Optional notes about this year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_years');
    }
};