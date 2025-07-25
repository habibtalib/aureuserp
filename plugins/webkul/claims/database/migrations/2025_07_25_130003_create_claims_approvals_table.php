<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('claim_id');
            $table->uuid('approver_id');
            $table->integer('level');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->bigInteger('company_id')->unsigned();
            $table->timestamps();

            // Indexes
            $table->index(['claim_id', 'level']);
            $table->index(['approver_id', 'status']);
            $table->index('company_id');

            // Unique constraint to prevent duplicate approvals at the same level
            $table->unique(['claim_id', 'level']);

            // Foreign keys
            $table->foreign('claim_id')->references('id')->on('claims_claims')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('employees_employees')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims_approvals');
    }
};