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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('product_units')->onDelete('restrict');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku', 100)->nullable();
            $table->bigInteger('unit_price')->default(0);
            $table->string('billing_type')->default('one_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('workspace_id');
            $table->index(['workspace_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
