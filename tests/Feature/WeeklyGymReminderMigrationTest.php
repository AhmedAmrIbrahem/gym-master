<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class WeeklyGymReminderMigrationTest extends TestCase
{
    public function test_upgrade_preserves_old_settings_and_history_and_refuses_destructive_rollback(): void
    {
        $previous = config('database.default');
        config(['database.connections.upgrade_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true], 'database.default' => 'upgrade_test']);
        Schema::clearResolvedInstance('db.schema');
        try {
            (require database_path('migrations/0001_01_01_000000_create_users_table.php'))->up();
            (require database_path('migrations/2026_10_30_000000_create_gym_reminders_tables.php'))->up();
            DB::table('users')->insert(['id' => 1, 'name' => 'Legacy', 'email' => 'legacy@example.test', 'password' => 'unused']);
            $version = (string) Str::uuid();
            DB::table('gym_reminders')->insert(['user_id' => 1, 'enabled' => true, 'time' => '19:45', 'timezone' => 'Africa/Cairo', 'version' => $version, 'consent_phone' => '+201012345678', 'consented_at' => now()]);
            $record = ['user_id' => 1, 'local_date' => '2026-10-30', 'settings_version' => $version, 'scheduled_at' => '2026-10-30 17:45:00', 'status' => 'sent'];
            DB::table('gym_reminder_deliveries')->insert($record + ['idempotency_key' => (string) Str::uuid()]);
            $migration = require database_path('migrations/2026_10_31_000000_add_weekly_gym_reminders.php');
            $migration->up();
            $row = DB::table('gym_reminders')->first();
            $this->assertSame(array_fill(0, 7, '19:45'), json_decode($row->weekly_schedule, true));
            $this->assertSame($version, $row->version);
            $this->assertSame('+201012345678', $row->consent_phone);
            $this->assertNotNull($row->eligible_after);
            $this->assertSame('sent', DB::table('gym_reminder_deliveries')->first()->status);
            $record['settings_version'] = (string) Str::uuid();
            DB::table('gym_reminder_deliveries')->insert($record + ['idempotency_key' => (string) Str::uuid()]);
            try {
                $migration->down();
                $this->fail('Rollback must not erase multiple revisions to restore daily uniqueness');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('no data was deleted', $exception->getMessage());
            }
            $this->assertSame(2, DB::table('gym_reminder_deliveries')->count());
            $this->assertTrue(Schema::hasColumn('gym_reminders', 'weekly_schedule'));
            DB::table('gym_reminder_deliveries')->where('settings_version', $record['settings_version'])->delete();
            $migration->down();
            $this->assertFalse(Schema::hasColumn('gym_reminders', 'weekly_schedule'));
            $this->assertSame('19:45', DB::table('gym_reminders')->first()->time);
            $this->assertSame(1, DB::table('gym_reminder_deliveries')->count());
        } finally {
            DB::disconnect('upgrade_test');
            config(['database.default' => $previous]);
            Schema::clearResolvedInstance('db.schema');
        }
    }
}
