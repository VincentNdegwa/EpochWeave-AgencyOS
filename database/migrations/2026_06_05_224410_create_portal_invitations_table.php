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
        Schema::create('portal_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('account_contact_id')->constrained('account_contacts')->onDelete('cascade');
            $table->string('invitation_token')->unique();
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index('invitation_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_invitations');
    }
};
