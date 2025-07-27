<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_salary_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['earning', 'deduction', 'employer_contribution']);
            $table->enum('calculation_type', ['fixed', 'percentage', 'computed', 'variable']);
            $table->decimal('default_amount', 12, 2)->nullable();
            $table->decimal('default_rate', 8, 4)->nullable(); // For percentage calculations
            $table->text('formula')->nullable(); // For computed calculations
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_provident_fund_applicable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('company_id');
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'is_active']);
            $table->index('code');
            $table->index('display_order');

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_salary_components');
    }
};