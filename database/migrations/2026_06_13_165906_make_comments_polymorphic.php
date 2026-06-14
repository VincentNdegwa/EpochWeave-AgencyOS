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
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['task_id']);
            $table->dropIndex('idx_comments_project');
            $table->dropIndex('idx_comments_task');
            $table->dropColumn(['project_id', 'task_id']);

            $table->morphs('commentable');
            $table->index('commentable_type');
            $table->index('commentable_id');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropMorphs('commentable');

            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->cascadeOnDelete();
            $table->index('project_id', 'idx_comments_project');
            $table->index('task_id', 'idx_comments_task');
        });
    }
};
