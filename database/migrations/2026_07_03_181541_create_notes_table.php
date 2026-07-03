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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('noteable_type');
            $table->unsignedBigInteger('noteable_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->timestampTz('pinned_at')->nullable();
            $table->timestampsTz();

            $table->index(['noteable_type', 'noteable_id'], 'idx_notes_noteable');
            $table->index('workspace_id', 'idx_notes_workspace');
            $table->index('user_id', 'idx_notes_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
