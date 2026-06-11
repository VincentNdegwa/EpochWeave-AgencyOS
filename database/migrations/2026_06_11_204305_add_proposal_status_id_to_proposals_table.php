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
            $table->dropColumn('status');
            $table->foreignId('proposal_status_id')->nullable()->after('proposal_number')->constrained('proposal_statuses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropForeign(['proposal_status_id']);
            $table->dropColumn('proposal_status_id');
            $table->string('status')->default('draft')->after('proposal_number');
        });
    }
};
