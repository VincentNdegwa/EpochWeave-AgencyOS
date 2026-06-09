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
        if (! Schema::hasTable('projects') || ! Schema::hasTable('proposals')) {
            return;
        }

        Schema::table('proposals', function (Blueprint $table) {
            $connection = Schema::getConnection()->getDriverName();

            if ($connection !== 'sqlite') {
                $table->foreign('project_id', 'proposals_project_id_foreign')
                    ->references('id')->on('projects')
                    ->nullOnDelete();
            }

            $table->index('project_id', 'idx_proposals_project');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('proposals')) {
            return;
        }

        Schema::table('proposals', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropForeign('proposals_project_id_foreign');
            }

            $table->dropIndex('idx_proposals_project');
        });
    }
};
