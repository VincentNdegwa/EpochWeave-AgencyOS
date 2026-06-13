<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            // Content
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('unit_label')->default('Pcs'); // Matches your "Pcs" unit

            // Quantity & Pricing
            $table->decimal('quantity', 12, 2)->default(1.00);
            $table->bigInteger('unit_price')->default(0);
            $table->bigInteger('subtotal')->default(0); // quantity * unit_price

            // Discount Logic (Matched to Proposal Items)
            $table->string('discount_type')->default('percentage'); // percentage, fixed
            $table->decimal('discount_value', 12, 2)->default(0.00);
            $table->bigInteger('discount_amount')->default(0); // The actual calculated deduction

            // Tax Logic (Matched to Proposal Items)
            $table->string('tax_type')->default('percentage'); // percentage, fixed
            $table->decimal('tax_value', 12, 2)->default(0.00);
            $table->bigInteger('total_tax_amount')->default(0); // The actual calculated tax

            // The Bottom Line for this row
            $table->bigInteger('total')->default(0); // (subtotal - discount) + tax

            $table->integer('position')->default(0); // For sort order
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
