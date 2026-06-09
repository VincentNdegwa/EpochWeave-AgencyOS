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
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();

            $table->string('name', 100);
            $table->string('color', 7)->default('#6366f1');
            $table->integer('position')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_closed')->default(false);

            $table->timestampTz('created_at')->useCurrent();

            $table->index(['project_id', 'position'], 'idx_task_statuses_project');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_statuses');
    }
};
