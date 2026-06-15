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
        Schema::create('project_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('title');
            $table->string('color', 7)->default('#6366f1');
            $table->boolean('is_system')->default(false);
            $table->string('automation_trigger', 30)->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index(['workspace_id', 'position'], 'idx_project_status_workspace_order');
            $table->unique(['workspace_id', 'automation_trigger'], 'uidx_project_status_system_trigger');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_statuses');
    }
};
