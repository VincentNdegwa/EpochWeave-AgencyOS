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
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->text('description')->nullable();
            $table->timestampTz('started_at');
            $table->timestampTz('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();

            $table->boolean('is_billable')->default(true);
            $table->boolean('is_invoiced')->default(false);
            $table->decimal('hourly_rate', 10, 2)->nullable();

            $table->date('date');
            $table->unsignedBigInteger('invoice_id')->nullable();

            $table->timestampsTz();

            $table->index('project_id', 'idx_time_entries_project');
            $table->index('task_id', 'idx_time_entries_task');
            $table->index('user_id', 'idx_time_entries_user');
            $table->index(['workspace_id', 'date'], 'idx_time_entries_date');
            $table->index(['project_id', 'is_billable', 'is_invoiced'], 'idx_time_entries_unbilled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
