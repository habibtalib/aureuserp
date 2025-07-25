<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims_claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('claim_number')->unique();
            $table->bigInteger('employee_id')->unsigned();
            $table->uuid('category_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'paid', 'cancelled'])->default('draft');
            $table->date('expense_date');
            $table->date('submitted_at')->nullable();
            $table->date('approved_at')->nullable();
            $table->date('paid_at')->nullable();
            $table->bigInteger('approved_by')->unsigned()->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['employee_id', 'status']);
            $table->index(['company_id', 'status']);
            $table->index(['expense_date']);
            $table->index('claim_number');

            // Foreign keys
            $table->foreign('employee_id')->references('id')->on('employees_employees')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('claims_categories')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('employees_employees')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims_claims');
    }
};