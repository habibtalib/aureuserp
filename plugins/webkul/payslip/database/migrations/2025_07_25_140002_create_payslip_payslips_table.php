<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_payslips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('payslip_number')->unique();
            $table->bigInteger('employee_id')->unsigned();
            $table->uuid('salary_structure_id');
            $table->integer('pay_year');
            $table->integer('pay_month');
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->enum('status', ['draft', 'pending', 'approved', 'paid', 'cancelled'])->default('draft');
            
            // Salary breakdown
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->decimal('employer_contributions', 12, 2)->default(0);
            
            // Attendance data
            $table->integer('total_working_days')->default(0);
            $table->integer('days_present')->default(0);
            $table->integer('days_absent')->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_amount', 12, 2)->default(0);
            
            // Tax information
            $table->decimal('taxable_income', 12, 2)->default(0);
            $table->decimal('tax_deducted', 12, 2)->default(0);
            $table->decimal('provident_fund', 12, 2)->default(0);
            
            // Processing information
            $table->date('processed_date')->nullable();
            $table->bigInteger('processed_by')->unsigned()->nullable();
            $table->date('approved_date')->nullable();
            $table->bigInteger('approved_by')->unsigned()->nullable();
            $table->date('paid_date')->nullable();
            $table->bigInteger('paid_by')->unsigned()->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            $table->json('calculation_details')->nullable(); // Store detailed calculation breakdown
            $table->string('pdf_path')->nullable();
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['employee_id', 'pay_year', 'pay_month']);
            $table->index(['company_id', 'status']);
            $table->index(['pay_year', 'pay_month']);
            $table->index('payslip_number');
            $table->index('status');

            // Unique constraint to prevent duplicate payslips
            $table->unique(['employee_id', 'pay_year', 'pay_month'], 'payslip_unique_employee_period');

            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('employees_employees')->onDelete('cascade');
            $table->foreign('salary_structure_id')->references('id')->on('payslip_salary_structures')->onDelete('restrict');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_payslips');
    }
};