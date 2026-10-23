<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // null = طعام عام للجميع
            $table->string('name');
            $table->decimal('calories_per_100g', 6, 2);
            $table->decimal('protein_per_100g', 6, 2);
            $table->decimal('carbs_per_100g', 6, 2)->default(0);
            $table->decimal('fats_per_100g', 6, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
