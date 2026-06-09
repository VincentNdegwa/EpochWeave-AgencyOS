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
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('role', 20)->default('member');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->timestampTz('joined_at')->useCurrent();

            $table->timestampsTz();

            $table->unique(['project_id', 'user_id']);
            $table->index('project_id', 'idx_project_members_project');
            $table->index('user_id', 'idx_project_members_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
