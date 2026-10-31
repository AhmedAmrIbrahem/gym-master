<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_photo_drafts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('items');
            $table->dateTime('expires_at')->index();
            $table->dateTime('consumed_at')->nullable();
            $table->foreignId('saved_meal_id')->nullable()->constrained('daily_meals')->nullOnDelete();
            $table->string('approved_hash', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_photo_drafts');
    }
};
