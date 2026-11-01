<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gym_reminders', function (Blueprint $table) {
            $table->json('weekly_schedule')->nullable();
            $table->timestamp('eligible_after')->nullable();
        });
        DB::table('gym_reminders')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('gym_reminders')->where('id', $row->id)->update([
                    'weekly_schedule' => json_encode(array_fill(0, 7, $row->time)),
                    'timezone' => 'Africa/Cairo',
                    'eligible_after' => now('UTC')->format('Y-m-d H:i:s'),
                ]);
            }
        });
        Schema::table('gym_reminder_deliveries', function (Blueprint $table) {
            $table->unique(['user_id', 'local_date', 'settings_version'], 'gym_reminder_occurrence_unique');
            $table->dropUnique(['user_id', 'local_date']);
            $table->text('message_text')->nullable();
        });
        Schema::create('gym_reminder_messages', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->text('text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (DB::table('gym_reminder_deliveries')->select('user_id', 'local_date')->groupBy('user_id', 'local_date')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Cannot restore the old daily unique constraint while multiple schedule revisions exist. Export/reconcile delivery history first; no data was deleted.');
        }
        Schema::dropIfExists('gym_reminder_messages');
        Schema::table('gym_reminder_deliveries', function (Blueprint $table) {
            $table->unique(['user_id', 'local_date']);
            $table->dropUnique('gym_reminder_occurrence_unique');
            $table->dropColumn('message_text');
        });
        Schema::table('gym_reminders', function (Blueprint $table) {
            $table->dropColumn(['weekly_schedule', 'eligible_after']);
        });
    }
};
