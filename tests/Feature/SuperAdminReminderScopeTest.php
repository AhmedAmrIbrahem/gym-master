<?php

namespace Tests\Feature;

use App\Jobs\SendGymReminder;
use App\Models\GymReminder;
use App\Models\GymReminderDelivery;
use App\Models\User;
use App\Services\GymReminders\WhatsAppSender;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class SuperAdminReminderScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_profile_omits_reminder_and_direct_update_is_forbidden(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->get(route('profile.edit'))->assertOk()
            ->assertSee('تعديل البيانات الشخصية')
            ->assertDontSee('gym-reminder-title', false)
            ->assertDontSee(route('profile.gym-reminder.update'), false);
        $this->putJson(route('profile.gym-reminder.update'), ['enabled' => 1])->assertForbidden();
        $this->assertDatabaseCount('gym_reminders', 0);
    }

    public function test_member_keeps_reminder_in_their_profile(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']))->get(route('profile.edit'))->assertOk()
            ->assertSee('gym-reminder-title', false)
            ->assertSee(route('profile.gym-reminder.update'), false);
    }

    public function test_legacy_admin_reminder_is_not_scheduled_and_previously_queued_job_is_skipped(): void
    {
        config(['gym_reminders.enabled' => true]);
        $now = CarbonImmutable::parse('2026-11-02 18:00:00', 'Africa/Cairo');
        $this->travelTo($now);
        $admin = User::factory()->create(['role' => 'super_admin', 'phone' => '+201012345678']);
        $version = (string) Str::uuid();
        GymReminder::create([
            'user_id' => $admin->id, 'enabled' => true, 'time' => '18:00', 'timezone' => 'Africa/Cairo',
            'version' => $version, 'consent_phone' => $admin->phone, 'consented_at' => now(),
        ]);
        Queue::fake();
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 0);
        Queue::assertNothingPushed();
        $delivery = GymReminderDelivery::create([
            'user_id' => $admin->id, 'local_date' => $now->toDateString(), 'settings_version' => $version,
            'idempotency_key' => (string) Str::uuid(), 'scheduled_at' => $now,
        ]);
        $sender = Mockery::mock(WhatsAppSender::class)->shouldNotReceive('send')->getMock();
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('skipped', $delivery->fresh()->status);
        $this->assertDatabaseCount('gym_reminders', 1);
    }
}
