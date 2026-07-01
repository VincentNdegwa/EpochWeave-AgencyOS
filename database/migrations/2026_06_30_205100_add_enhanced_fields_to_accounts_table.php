<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('company_name');
            $table->text('description')->nullable()->after('website');
            $table->date('founded_at')->nullable()->after('description');
            $table->decimal('annual_revenue', 15, 2)->nullable()->after('lifetime_value');
            $table->unsignedInteger('employee_count')->nullable()->after('annual_revenue');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['phone', 'description', 'founded_at', 'annual_revenue', 'employee_count']);
        });
    }
};
