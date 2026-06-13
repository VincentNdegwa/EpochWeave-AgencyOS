<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('proposals', 'tax_rate')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->dropColumn('tax_rate');
            });
        }

        if (Schema::hasColumn('proposals', 'tax_amount')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->dropColumn('tax_amount');
            });
        }

        if (! Schema::hasColumn('proposals', 'total_tax_amount')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->decimal('total_tax_amount', 10, 2)->nullable();
            });
        }

        if (Schema::hasColumn('proposal_items', 'tax_rate')) {
            Schema::table('proposal_items', function (Blueprint $table) {
                $table->dropColumn('tax_rate');
            });
        }

        if (Schema::hasColumn('proposal_items', 'tax_amount')) {
            Schema::table('proposal_items', function (Blueprint $table) {
                $table->dropColumn('tax_amount');
            });
        }

        if (! Schema::hasColumn('proposal_items', 'tax_type')) {
            Schema::table('proposal_items', function (Blueprint $table) {
                $table->string('tax_type')->nullable();
            });
        }

        if (! Schema::hasColumn('proposal_items', 'tax_value')) {
            Schema::table('proposal_items', function (Blueprint $table) {
                $table->decimal('tax_value', 10, 2)->nullable();
            });
        }

        if (! Schema::hasColumn('proposal_items', 'total_tax_amount')) {
            Schema::table('proposal_items', function (Blueprint $table) {
                $table->decimal('total_tax_amount', 10, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('proposal_items', 'total_tax_amount')) {
            Schema::table('proposal_items', function (Blueprint $table) {
                $table->dropColumn('total_tax_amount');
            });
        }

        if (Schema::hasColumn('proposals', 'total_tax_amount')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->dropColumn('total_tax_amount');
            });
        }

        if (! Schema::hasColumn('proposals', 'tax_rate')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->decimal('tax_rate', 5, 2)->nullable();
            });
        }

        if (! Schema::hasColumn('proposals', 'tax_amount')) {
            Schema::table('proposals', function (Blueprint $table) {
                $table->decimal('tax_amount', 10, 2)->nullable();
            });
        }

        if (Schema::hasColumn('proposal_items', 'tax_type')) {
            Schema::table('proposal_items', function (Blueprint $table) {
                $table->dropColumn('tax_type');
            });
        }

        if (Schema::hasColumn('proposal_items', 'tax_value')) {
            Schema::table('proposal_items', function (Blueprint $table) {
                $table->dropColumn('tax_value');
            });
        }

        if (! Schema::hasColumn('proposal_items', 'tax_rate')) {
            Schema::table('proposal_items', function (Blueprint $table) {
                $table->decimal('tax_rate', 5, 2)->nullable();
            });
        }

        if (! Schema::hasColumn('proposal_items', 'tax_amount')) {
            Schema::table('proposal_items', function (Blueprint $table) {
                $table->decimal('tax_amount', 10, 2)->nullable();
            });
        }
    }
};
