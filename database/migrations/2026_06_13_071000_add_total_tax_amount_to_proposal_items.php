<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposal_items', function (Blueprint $table) {
            if (! Schema::hasColumn('proposal_items', 'total_tax_amount')) {
                $table->decimal('total_tax_amount', 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('proposal_items', function (Blueprint $table) {
            if (Schema::hasColumn('proposal_items', 'total_tax_amount')) {
                $table->dropColumn('total_tax_amount');
            }
        });
    }
};
