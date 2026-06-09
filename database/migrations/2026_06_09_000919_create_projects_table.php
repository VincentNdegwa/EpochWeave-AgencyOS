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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#6366f1');

            $table->string('status', 30)->default('active');

            $table->string('billing_type', 20)->default('hourly');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->unsignedBigInteger('budget')->nullable();
            $table->unsignedBigInteger('retainer_amount')->nullable();
            $table->string('currency', 10)->default('KES');

            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();

            $table->unsignedInteger('tasks_total')->default(0);
            $table->unsignedInteger('tasks_completed')->default(0);
            $table->decimal('hours_logged', 8, 2)->default(0);
            $table->decimal('hours_budgeted', 8, 2)->nullable();

            $table->boolean('portal_visible')->default(true);

            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('archived_at')->nullable();

            $table->timestampsTz();

            $table->index('workspace_id', 'idx_projects_workspace');
            $table->index('account_id', 'idx_projects_account');
            $table->index(['workspace_id', 'status'], 'idx_projects_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
