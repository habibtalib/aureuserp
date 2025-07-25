<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_bill_of_materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('reference')->unique();
            $table->uuid('product_id');
            $table->string('version')->default('1.0');
            $table->enum('type', ['standard', 'kit', 'phantom', 'assembly'])->default('standard');
            $table->enum('state', ['draft', 'active', 'obsolete', 'archived'])->default('draft');
            $table->decimal('quantity_to_produce', 10, 4)->default(1);
            $table->uuid('unit_id')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('company_id');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['product_id', 'state']);
            $table->index(['company_id', 'state']);
            $table->index(['effective_date', 'expiry_date']);
            $table->index('reference');

            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products_products')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('products_uoms')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_bill_of_materials');
    }
};