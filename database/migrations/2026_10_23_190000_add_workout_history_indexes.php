<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'workout_sessions_user_created_idx');
        });

        Schema::table('workout_logs', function (Blueprint $table) {
            $table->index(['exercise_id', 'created_at'], 'workout_logs_exercise_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('workout_logs', function (Blueprint $table) {
            $table->dropIndex('workout_logs_exercise_created_idx');
        });

        Schema::table('workout_sessions', function (Blueprint $table) {
            $table->dropIndex('workout_sessions_user_created_idx');
        });
    }
};
