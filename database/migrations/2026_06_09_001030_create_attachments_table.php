<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->morphs('attachable');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();

            $table->string('disk', 50)->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('extension', 10)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->json('meta')->nullable();

            $table->timestampsTz();

            $table->index('uploaded_by', 'idx_attachments_uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
