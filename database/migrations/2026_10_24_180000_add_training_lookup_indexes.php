<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->index(['user_id', 'workout_day_id', 'date'], 'workout_sessions_user_day_date_idx');
        });

        Schema::table('workout_logs', function (Blueprint $table) {
            $table->index(['workout_session_id', 'exercise_id', 'set_number'], 'workout_logs_session_exercise_set_idx');
        });
    }

    public function down(): void
    {
        Schema::table('workout_logs', function (Blueprint $table) {
            $table->dropIndex('workout_logs_session_exercise_set_idx');
        });

        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->dropIndex('workout_sessions_user_day_date_idx');
        });
    }
};
