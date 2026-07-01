<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_profiles', function (Blueprint $table) {
            $table->id();
            $table->morphs('profileable');
            $table->string('platform');
            $table->string('url');
            $table->string('handle')->nullable();
            $table->unsignedBigInteger('followers_count')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index(['profileable_type', 'profileable_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_profiles');
    }
};
