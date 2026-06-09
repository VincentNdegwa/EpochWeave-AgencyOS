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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('template_id')->nullable()->constrained('proposal_templates')->onDelete('set null');

            $table->string('title');
            $table->string('proposal_number');
            $table->string('status')->default('draft');
            $table->date('valid_until')->nullable();
            $table->json('content');

            $table->string('currency')->default('KES');
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('discount_total')->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('grand_total')->default(0);

            $table->boolean('requires_deposit')->default(false);
            $table->string('deposit_type')->nullable();
            $table->decimal('deposit_value', 10, 2)->nullable();
            $table->bigInteger('deposit_amount')->nullable();

            $table->string('token')->unique();
            $table->string('password_hash')->nullable();

            $table->string('signer_name')->nullable();
            $table->string('signer_email')->nullable();
            $table->string('signer_company')->nullable();
            $table->text('signature_data')->nullable();
            $table->string('signed_ip')->nullable();
            $table->text('signed_user_agent')->nullable();

            $table->foreignId('deposit_invoice_id')->nullable();
            $table->foreignId('project_id')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('last_viewed_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->text('decline_reason')->nullable();

            $table->timestamps();

            $table->index('workspace_id');
            $table->index('account_id');
            $table->index('token');
            $table->index(['workspace_id', 'status']);
            $table->index('valid_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
