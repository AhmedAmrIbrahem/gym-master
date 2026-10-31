<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PersonalReminderMigrationTest extends TestCase
{
    public function test_upgrade_preserves_send_times_and_old_revision_history_and_safe_rollback_refuses_data_loss(): void
    {
        $previous = config('database.default');
        config(['database.connections.offset_upgrade' => ['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true], 'database.default' => 'offset_upgrade']);
        Schema::clearResolvedInstance('db.schema');
        try {
            foreach (['0001_01_01_000000_create_users_table.php', '2026_10_30_000000_create_gym_reminders_tables.php', '2026_10_31_000000_add_weekly_gym_reminders.php'] as $file) {
                (require database_path('migrations/'.$file))->up();
            }
            DB::table('users')->insert(['id' => 1, 'name' => 'Legacy', 'email' => 'legacy@example.test', 'password' => 'unused']);
            $version = (string) Str::uuid();
            DB::table('gym_reminders')->insert(['user_id' => 1, 'enabled' => true, 'time' => '17:00', 'timezone' => 'Africa/Cairo', 'weekly_schedule' => '["17:00"]', 'version' => $version]);
            $row = ['user_id' => 1, 'local_date' => '2026-11-01', 'scheduled_at' => '2026-11-01 15:00:00', 'status' => 'unknown'];
            foreach ([$version, (string) Str::uuid()] as $v) {
                DB::table('gym_reminder_deliveries')->insert($row + ['settings_version' => $v, 'idempotency_key' => (string) Str::uuid()]);
            }
            $migration = require database_path('migrations/2026_10_31_020000_add_personal_reminder_offsets.php');
            $migration->up();
            $this->assertSame([0], json_decode(DB::table('gym_reminders')->first()->reminder_offsets, true));
            $this->assertSame($version, DB::table('gym_reminders')->first()->version);
            $this->assertSame(2, DB::table('gym_reminder_deliveries')->count());
            $this->assertSame('2026-11-01 15:00:00', DB::table('gym_reminder_deliveries')->first()->gym_at);
            DB::table('gym_reminder_deliveries')->insert($row + ['settings_version' => $version, 'idempotency_key' => (string) Str::uuid(), 'offset_minutes' => 15, 'occurrence_key' => hash('sha256', 'new slot')]);
            try {
                $migration->down();
                $this->fail('Rollback must not erase a second reminder');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('nothing was deleted', $exception->getMessage());
            }
            $this->assertSame(3, DB::table('gym_reminder_deliveries')->count());
            DB::table('gym_reminder_deliveries')->where('offset_minutes', 15)->delete();
            $migration->down();
            $this->assertFalse(Schema::hasColumn('gym_reminders', 'reminder_offsets'));
            $this->assertSame(2, DB::table('gym_reminder_deliveries')->count());
        } finally {
            DB::purge('offset_upgrade');
            config(['database.default' => $previous]);
            Schema::clearResolvedInstance('db.schema');
        }
    }
}
