<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('direction')->default('outbound');
            $table->string('status')->default('completed');
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->foreignId('proposal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('follow_up_at')->nullable();
            $table->string('outcome')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'account_id', 'completed_at']);
            $table->index(['workspace_id', 'account_id', 'follow_up_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagements');
    }
};
