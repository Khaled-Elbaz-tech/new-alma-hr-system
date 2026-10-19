<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_name');
            $table->string('field_label');
            $table->enum('field_type', [
                'text',
                'textarea',
                'number',
                'date',
                'email',
                'phone',
                'attachment',
                'checkbox',
                'radio',
                'select',
                'yes_no'
            ]);
            $table->text('field_options')->nullable(); // For select, radio options (JSON)
            $table->string('field_section')->default('general'); // Group fields into sections
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('employee_can_view')->default(true);
            $table->boolean('employee_can_edit')->default(false);
            $table->boolean('hr_can_view')->default(true);
            $table->boolean('hr_can_edit')->default(true);
            $table->integer('field_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_fields');
    }
};
