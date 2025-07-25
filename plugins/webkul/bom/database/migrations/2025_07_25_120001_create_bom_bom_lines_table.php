<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_bom_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bom_id');
            $table->bigInteger('product_id')->unsigned();
            $table->decimal('quantity', 10, 4);
            $table->bigInteger('unit_id')->unsigned();
            $table->integer('sequence')->default(10);
            $table->enum('component_type', ['material', 'component', 'sub_assembly', 'consumable', 'byproduct'])->default('material');
            $table->uuid('sub_bom_id')->nullable();
            $table->decimal('waste_percentage', 5, 2)->default(0);
            $table->boolean('is_optional')->default(false);
            $table->text('notes')->nullable();
            $table->bigInteger('company_id')->unsigned();
            $table->timestamps();

            // Indexes
            $table->index(['bom_id', 'sequence']);
            $table->index(['product_id', 'component_type']);
            $table->index('company_id');

            // Foreign keys
            $table->foreign('bom_id')->references('id')->on('bom_bill_of_materials')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products_products')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('unit_of_measures')->onDelete('cascade');
            $table->foreign('sub_bom_id')->references('id')->on('bom_bill_of_materials')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_bom_lines');
    }
};