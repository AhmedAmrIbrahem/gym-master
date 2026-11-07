<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only alter defaults and nullability. Do not overwrite existing members' goals.
        Schema::table('users', function (Blueprint $table) {
            $table->integer('target_calories')->nullable()->default(null)->change();
            $table->decimal('target_protein', 8, 1)->nullable()->default(null)->change();
            $table->decimal('target_carbs', 8, 1)->nullable()->default(null)->change();
            $table->decimal('target_fats', 8, 1)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        // Existing NULLs cannot safely be converted back to NOT NULL without
        // inventing nutrition targets. Leave the nullable columns unchanged.
    }
};
