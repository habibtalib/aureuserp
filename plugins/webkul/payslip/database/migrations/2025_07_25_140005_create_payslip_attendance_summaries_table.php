<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_attendance_summaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('employee_id')->unsigned();
            $table->integer('year');
            $table->integer('month');
            $table->integer('total_working_days');
            $table->integer('days_present');
            $table->integer('days_absent');
            $table->integer('days_weekend');
            $table->integer('days_holiday');
            $table->integer('days_leave');
            $table->decimal('regular_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('late_hours', 8, 2)->default(0);
            $table->decimal('early_departure_hours', 8, 2)->default(0);
            $table->json('daily_breakdown')->nullable(); // Store day-wise attendance data
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->bigInteger('finalized_by')->unsigned()->nullable();
            $table->bigInteger('company_id')->unsigned();
            $table->timestamps();

            // Indexes
            $table->index(['employee_id', 'year', 'month']);
            $table->index(['company_id', 'year', 'month']);
            $table->index('is_finalized');

            // Unique constraint
            $table->unique(['employee_id', 'year', 'month'], 'attendance_unique_employee_period');

            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('employees_employees')->onDelete('cascade');
            $table->foreign('finalized_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_attendance_summaries');
    }
};