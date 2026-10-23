<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_programs', function (Blueprint $table) {
            $table->index(['user_id', 'is_active'], 'workout_programs_user_active_idx');
        });

        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'workout_sessions_user_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->dropIndex('workout_sessions_user_date_idx');
        });

        Schema::table('workout_programs', function (Blueprint $table) {
            $table->dropIndex('workout_programs_user_active_idx');
        });
    }
};
