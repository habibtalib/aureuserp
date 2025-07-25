<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims_claim_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('claim_id');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('expense_date');
            $table->string('receipt_reference')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sequence')->default(10);
            $table->bigInteger('company_id')->unsigned();
            $table->timestamps();

            // Indexes
            $table->index(['claim_id', 'sequence']);
            $table->index('expense_date');
            $table->index('company_id');

            // Foreign keys
            $table->foreign('claim_id')->references('id')->on('claims_claims')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims_claim_lines');
    }
};