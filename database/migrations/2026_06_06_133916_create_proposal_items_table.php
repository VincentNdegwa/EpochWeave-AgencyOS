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
        Schema::create('proposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('unit_label');
            $table->string('billing_type');

            $table->decimal('quantity', 10, 2)->default(1);
            $table->bigInteger('unit_price');
            $table->bigInteger('subtotal');

            $table->string('discount_type')->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->bigInteger('discount_amount')->default(0);

            $table->bigInteger('total');

            $table->boolean('is_optional')->default(false);
            $table->boolean('is_selected')->default(true);
            $table->integer('position')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_items');
    }
};
