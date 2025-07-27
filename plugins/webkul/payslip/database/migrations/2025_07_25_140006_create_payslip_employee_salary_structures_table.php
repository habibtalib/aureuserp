<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_employee_salary_structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('employee_id')->unsigned();
            $table->uuid('salary_structure_id');
            $table->decimal('basic_salary', 12, 2);
            $table->json('custom_allowances')->nullable(); // Employee-specific allowance amounts
            $table->json('custom_deductions')->nullable(); // Employee-specific deduction amounts
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['employee_id', 'is_active'], 'emp_sal_struct_emp_active_idx');
            $table->index(['employee_id', 'effective_from', 'effective_to'], 'emp_sal_struct_emp_dates_idx');
            $table->index('salary_structure_id', 'emp_sal_struct_struct_idx');
            $table->index('company_id', 'emp_sal_struct_company_idx');

            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('employees_employees')->onDelete('cascade');
            $table->foreign('salary_structure_id')->references('id')->on('payslip_salary_structures')->onDelete('restrict');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_employee_salary_structures');
    }
};