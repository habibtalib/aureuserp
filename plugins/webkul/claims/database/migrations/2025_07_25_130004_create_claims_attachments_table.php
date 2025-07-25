<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('claim_id');
            $table->uuid('claim_line_id')->nullable();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('path');
            $table->string('disk')->default('local');
            $table->text('description')->nullable();
            $table->bigInteger('company_id')->unsigned();
            $table->bigInteger('uploaded_by')->unsigned()->nullable();
            $table->timestamps();

            // Indexes
            $table->index('claim_id');
            $table->index('claim_line_id');
            $table->index('company_id');

            // Foreign keys
            $table->foreign('claim_id')->references('id')->on('claims_claims')->onDelete('cascade');
            $table->foreign('claim_line_id')->references('id')->on('claims_claim_lines')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims_attachments');
    }
};