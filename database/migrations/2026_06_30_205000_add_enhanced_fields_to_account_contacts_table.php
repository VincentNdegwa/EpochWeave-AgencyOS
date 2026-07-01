<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_contacts', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('job_title');
            $table->string('department')->nullable()->after('date_of_birth');
            $table->string('preferred_contact_method')->nullable()->after('department');
            $table->text('notes')->nullable()->after('preferred_contact_method');
        });
    }

    public function down(): void
    {
        Schema::table('account_contacts', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'department', 'preferred_contact_method', 'notes']);
        });
    }
};
