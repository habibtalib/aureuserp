<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_payslip_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('payslip_id');
            $table->uuid('salary_component_id');
            $table->string('component_name');
            $table->string('component_code');
            $table->enum('component_type', ['earning', 'deduction', 'employer_contribution']);
            $table->decimal('base_amount', 12, 2)->default(0); // Base amount for calculation
            $table->decimal('rate', 8, 4)->nullable(); // Rate used for percentage calculations
            $table->decimal('calculated_amount', 12, 2)->default(0); // Final calculated amount
            $table->text('calculation_notes')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('payslip_id');
            $table->index(['payslip_id', 'component_type']);
            $table->index('salary_component_id');
            $table->index('display_order');

            // Foreign keys
            $table->foreign('payslip_id')->references('id')->on('payslip_payslips')->onDelete('cascade');
            $table->foreign('salary_component_id')->references('id')->on('payslip_salary_components')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_payslip_items');
    }
};