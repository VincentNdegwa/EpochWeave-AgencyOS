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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('task_status_id')->constrained('task_statuses');
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->cascadeOnDelete();

            $table->string('title', 500);
            $table->text('description')->nullable();

            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('priority', 20)->default('medium');
            $table->integer('position')->default(0);

            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('estimated_hours', 6, 2)->nullable();

            $table->boolean('is_billable')->default(true);
            $table->timestampTz('completed_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();

            $table->index('project_id', 'idx_tasks_project');
            $table->index(['task_status_id', 'position'], 'idx_tasks_status');
            $table->index('assignee_id', 'idx_tasks_assignee');
            $table->index('parent_id', 'idx_tasks_parent');
            $table->index('due_date', 'idx_tasks_open_due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
