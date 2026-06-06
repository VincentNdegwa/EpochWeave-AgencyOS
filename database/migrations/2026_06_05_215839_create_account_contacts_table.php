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
        Schema::create('account_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('client_profile_id')->nullable()->constrained('client_profiles')->onDelete('set null');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('job_title')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->boolean('receives_billing')->default(true);
            $table->timestamps();
            $table->index('account_id');
            $table->index('client_profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_contacts');
    }
};
