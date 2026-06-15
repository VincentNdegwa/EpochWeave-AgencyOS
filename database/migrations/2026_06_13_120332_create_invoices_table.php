<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Contextual Links
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_contact_id')->nullable()->constrained()->onDelete('set null');

            // Source Tracking (The Weave)
            $table->foreignId('proposal_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');

            // Staff Attribution
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('user_id')->nullable()->constrained('users'); // Account Manager

            // Public Identification
            $table->string('invoice_number')->unique(); // e.g. INV2026-0001
            $table->foreignId('invoice_status_id')->constrained('invoice_statuses')->restrictOnDelete();
            $table->string('token')->unique(); // For public payment link

            // High-Level Financials (Matched to Proposals)
            $table->string('currency', 3)->default('USD');
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('total_tax_amount')->default(0);
            $table->bigInteger('discount_total')->default(0);
            $table->bigInteger('grand_total')->default(0);
            $table->bigInteger('amount_paid')->default(0); // For partial payments

            // Dates & Lifecycle
            $table->date('issue_date');
            $table->date('due_date');
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();

            $table->timestamps();

            // Performance Indexes
            $table->index(['workspace_id', 'invoice_status_id']);
            $table->index(['account_id', 'invoice_status_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
