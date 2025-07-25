<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_bom_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bom_id');
            $table->string('version_number');
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('change_description')->nullable();
            $table->text('change_reason')->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('company_id')->unsigned();
            $table->timestamps();

            // Indexes
            $table->index(['bom_id', 'version_number']);
            $table->index(['bom_id', 'effective_date']);
            $table->index('company_id');

            // Unique constraint for BOM version combination
            $table->unique(['bom_id', 'version_number']);

            // Foreign keys
            $table->foreign('bom_id')->references('id')->on('bom_bill_of_materials')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_bom_versions');
    }
};