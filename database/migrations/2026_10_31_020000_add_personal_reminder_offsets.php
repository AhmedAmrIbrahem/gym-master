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
            $table->json('reminder_offsets')->nullable();
        });
        // Preserve the exact send times of existing subscriptions; no surprise earlier messages.
        DB::table('gym_reminders')->update(['reminder_offsets' => json_encode([0])]);
        Schema::table('gym_reminder_deliveries', function (Blueprint $table) {
            $table->dateTime('gym_at')->nullable();
            $table->unsignedSmallInteger('offset_minutes')->default(0);
            $table->string('occurrence_key', 64)->nullable()->unique();
            $table->index(['user_id', 'local_date'], 'gym_reminder_day_index');
            $table->dropUnique('gym_reminder_occurrence_unique');
        });
        DB::table('gym_reminder_deliveries')->update(['gym_at' => DB::raw('scheduled_at')]);
    }

    public function down(): void
    {
        if (DB::table('gym_reminder_deliveries')->select('user_id', 'local_date', 'settings_version')
            ->groupBy('user_id', 'local_date', 'settings_version')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Multiple reminders exist for one gym day. Export/reconcile delivery history before rollback; nothing was deleted.');
        }
        Schema::table('gym_reminder_deliveries', function (Blueprint $table) {
            $table->unique(['user_id', 'local_date', 'settings_version'], 'gym_reminder_occurrence_unique');
            $table->dropUnique(['occurrence_key']);
            $table->dropIndex('gym_reminder_day_index');
            $table->dropColumn(['gym_at', 'offset_minutes', 'occurrence_key']);
        });
        Schema::table('gym_reminders', fn (Blueprint $table) => $table->dropColumn('reminder_offsets'));
    }
};
