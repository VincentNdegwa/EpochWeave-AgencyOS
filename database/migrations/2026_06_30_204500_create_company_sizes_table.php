<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('min_employees')->nullable();
            $table->unsignedInteger('max_employees')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['workspace_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_sizes');
    }
};
