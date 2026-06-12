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
        Schema::table('proposals', function (Blueprint $table) {
            $table->foreignId('account_contact_id')
                ->nullable()
                ->after('account_id')
                ->constrained('account_contacts')
                ->onDelete('set null');

            $table->foreignId('user_id')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->onDelete('set null');

            $table->index(['workspace_id', 'user_id'], 'idx_proposals_team_assignment');
            $table->index(['account_id', 'account_contact_id'], 'idx_proposals_client_history');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropIndex('idx_proposals_team_assignment');
            $table->dropIndex('idx_proposals_client_history');

            $table->dropForeign(['account_contact_id']);
            $table->dropForeign(['user_id']);

            $table->dropColumn(['account_contact_id', 'user_id']);
        });
    }
};
