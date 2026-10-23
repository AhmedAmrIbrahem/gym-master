<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('rest_target_calories')->nullable()->after('target_fats');
            $table->decimal('rest_target_protein', 8, 1)->nullable()->after('rest_target_calories');
            $table->decimal('rest_target_carbs', 8, 1)->nullable()->after('rest_target_protein');
            $table->decimal('rest_target_fats', 8, 1)->nullable()->after('rest_target_carbs');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rest_target_calories', 'rest_target_protein', 'rest_target_carbs', 'rest_target_fats']);
        });
    }
};
