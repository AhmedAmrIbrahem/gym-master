<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('target_calories')->default(2000)->after('email');
            $table->decimal('target_protein', 8, 1)->default(150.0)->after('target_calories');
            $table->decimal('target_carbs', 8, 1)->default(200.0)->after('target_protein');
            $table->decimal('target_fats', 8, 1)->default(60.0)->after('target_carbs');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['target_calories', 'target_protein', 'target_carbs', 'target_fats']);
        });
    }
};
