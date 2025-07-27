<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_salary_structures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('pay_period', ['monthly', 'bi_weekly', 'weekly', 'annual'])->default('monthly');
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->json('allowances')->nullable(); // Store allowance configurations
            $table->json('deductions')->nullable(); // Store deduction configurations
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('company_id');
            $table->index(['company_id', 'is_active']);
            $table->index('code');

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_salary_structures');
    }
};