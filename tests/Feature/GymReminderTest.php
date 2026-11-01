<?php

namespace Tests\Feature;

use App\Jobs\SendGymReminder;
use App\Models\GymReminder;
use App\Models\GymReminderDelivery;
use App\Models\User;
use App\Services\GymReminders\PhoneNumber;
use App\Services\GymReminders\WebWhatsAppSender;
use App\Services\GymReminders\WhatsAppSender;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class GymReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['gym_reminders.enabled' => true]);
        $this->travelTo(CarbonImmutable::parse('2026-10-30 16:00:00', 'UTC'));
        Http::preventStrayRequests();
    }

    private function userWithReminder(array $settings = []): User
    {
        $user = User::factory()->create(['phone' => '+201012345678']);
        GymReminder::create(array_merge([
            'user_id' => $user->id, 'enabled' => true, 'time' => '18:00', 'timezone' => 'Africa/Cairo',
            'consent_phone' => $user->phone, 'consented_at' => now(), 'version' => (string) Str::uuid(),
        ], $settings));

        return $user;
    }

    private function dueDelivery(): GymReminderDelivery
    {
        Queue::fake();
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();

        return GymReminderDelivery::sole();
    }

    private function days(string $time = '18:00'): array
    {
        return array_fill(0, 7, ['enabled' => 1, 'time' => $time]);
    }

    public function test_settings_are_scoped_to_authenticated_user_and_ignore_forged_ids_and_phone(): void
    {
        $other = $this->userWithReminder();
        $user = User::factory()->create(['phone' => '+201112345678']);
        $this->actingAs($user)->put(route('profile.gym-reminder.update'), [
            'enabled' => '1', 'days' => $this->days('19:30'), 'timezone' => 'Asia/Riyadh', 'opt_in' => '1',
            'user_id' => $other->id, 'phone' => '+19999999999', 'id' => 1,
        ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('gym_reminders', ['user_id' => $user->id, 'time' => '19:30', 'consent_phone' => $user->phone]);
        $this->assertDatabaseHas('gym_reminders', ['user_id' => $other->id, 'time' => '18:00']);
        $this->assertDatabaseCount('gym_reminders', 2);
    }

    public function test_requires_opt_in_valid_day_times_and_registered_phone(): void
    {
        $user = User::factory()->create(['phone' => null]);
        $this->actingAs($user)->put(route('profile.gym-reminder.update'), [
            'enabled' => 1, 'days' => $this->days('26:00'),
        ])->assertSessionHasErrorsIn('gymReminder', ['days.0.time', 'opt_in']);
        $this->put(route('profile.gym-reminder.update'), [
            'enabled' => 1, 'days' => $this->days(), 'opt_in' => 1,
        ])->assertSessionHasErrorsIn('gymReminder', 'enabled');
        $this->assertDatabaseCount('gym_reminders', 0);
    }

    public function test_can_disable_and_withdraw_consent_without_checking_opt_in(): void
    {
        $user = $this->userWithReminder();
        $delivery = $this->dueDelivery();
        $this->actingAs($user)->put(route('profile.gym-reminder.update'), [
            'enabled' => 0, 'days' => $this->days(),
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('gym_reminders', ['user_id' => $user->id, 'enabled' => false, 'consented_at' => null]);
        $sender = Mockery::mock(WhatsAppSender::class)->shouldNotReceive('send')->getMock();
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('skipped', $delivery->fresh()->status);
    }

    public function test_guest_and_inactive_account_cannot_save(): void
    {
        $this->put(route('profile.gym-reminder.update'), [])->assertRedirect(route('login'));
        $user = User::factory()->create(['is_active' => false]);
        $this->actingAs($user)->put(route('profile.gym-reminder.update'), [])->assertRedirect(route('login'));
        $this->assertDatabaseCount('gym_reminders', 0);
    }

    public function test_profile_shows_only_current_users_reminder(): void
    {
        $other = $this->userWithReminder(['time' => '23:47']);
        $user = User::factory()->create(['phone' => '+201112345678']);
        $this->actingAs($user)->get(route('profile.edit'))->assertOk()
            ->assertSee('تذكير موعد الجيم')->assertSee($user->phone)->assertDontSee($other->phone)->assertDontSee('23:47');
    }

    public function test_dispatch_honors_timezone_and_database_daily_uniqueness(): void
    {
        Queue::fake();
        $this->userWithReminder();
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 1);
        $this->assertSame('2026-10-30 16:00:00', GymReminderDelivery::sole()->scheduled_at->utc()->format('Y-m-d H:i:s'));
        Queue::assertPushed(SendGymReminder::class);
    }

    public function test_future_and_expired_schedules_do_not_queue(): void
    {
        Queue::fake();
        $this->userWithReminder(['time' => '18:01']);
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 0);
        GymReminder::query()->update(['time' => '17:49']);
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 0);
        Queue::assertNothingPushed();
    }

    public function test_dst_repeat_does_not_create_second_occurrence(): void
    {
        Queue::fake();
        $this->userWithReminder(['time' => '23:30', 'timezone' => 'Africa/Cairo']);
        $this->travelTo(CarbonImmutable::parse('2026-10-29 20:30:00', 'UTC'));
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->travelTo(CarbonImmutable::parse('2026-10-29 21:30:00', 'UTC'));
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 1);
    }

    public function test_send_claim_is_once_only_and_targets_registered_number(): void
    {
        $user = $this->userWithReminder();
        $delivery = $this->dueDelivery();
        $sender = Mockery::mock(WhatsAppSender::class);
        $sender->shouldReceive('send')->once()->with($delivery->idempotency_key, $user->phone, Mockery::type('string'))->andReturn('sent');
        $scheduled = $delivery->getRawOriginal('scheduled_at');
        (new SendGymReminder($delivery->id))->handle($sender);
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('sent', $delivery->fresh()->status);
        $this->assertNotNull($delivery->fresh()->sent_at);
        $this->assertSame($scheduled, $delivery->fresh()->getRawOriginal('scheduled_at'));
    }

    public function test_phone_change_revokes_delivery_until_fresh_consent(): void
    {
        $user = $this->userWithReminder();
        $delivery = $this->dueDelivery();
        $user->update(['phone' => '+201112345678']);
        $sender = Mockery::mock(WhatsAppSender::class)->shouldNotReceive('send')->getMock();
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('skipped', $delivery->fresh()->status);
    }

    public function test_inactive_user_and_global_kill_switch_block_queued_send(): void
    {
        $user = $this->userWithReminder();
        $delivery = $this->dueDelivery();
        $user->update(['is_active' => false]);
        $sender = Mockery::mock(WhatsAppSender::class)->shouldNotReceive('send')->getMock();
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('skipped', $delivery->fresh()->status);
        $user->update(['is_active' => true]);
        $delivery->update(['status' => 'pending']);
        config(['gym_reminders.enabled' => false]);
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('skipped', $delivery->fresh()->status);
    }

    public function test_queued_delivery_with_old_settings_or_expired_is_skipped(): void
    {
        $this->userWithReminder();
        $delivery = $this->dueDelivery();
        GymReminder::query()->update(['version' => (string) Str::uuid()]);
        $sender = Mockery::mock(WhatsAppSender::class)->shouldNotReceive('send')->getMock();
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('skipped', $delivery->fresh()->status);
        $delivery->update(['status' => 'pending', 'settings_version' => GymReminder::sole()->version]);
        $this->travel(11)->minutes();
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('skipped', $delivery->fresh()->status);
    }

    public function test_only_explicit_not_ready_can_be_retried_and_outbox_recovers_it(): void
    {
        $this->userWithReminder();
        $delivery = $this->dueDelivery();
        $sender = Mockery::mock(WhatsAppSender::class);
        $sender->shouldReceive('send')->twice()->andReturn('retry', 'sent');
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('pending', $delivery->fresh()->status);
        Queue::fake();
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        Queue::assertPushed(SendGymReminder::class, fn ($job) => $job->deliveryId === $delivery->id);
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('sent', $delivery->fresh()->status);
    }

    public function test_unknown_and_interrupted_sends_are_never_blindly_retried(): void
    {
        $this->userWithReminder();
        $delivery = $this->dueDelivery();
        $sender = Mockery::mock(WhatsAppSender::class);
        $sender->shouldReceive('send')->once()->andReturn('unknown');
        (new SendGymReminder($delivery->id))->handle($sender);
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('unknown', $delivery->fresh()->status);
        $delivery->update(['status' => 'sending']);
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('sending', $delivery->fresh()->status);
    }

    public function test_normalizes_only_supported_registered_numbers(): void
    {
        $this->assertSame('+201012345678', PhoneNumber::normalize('010 1234 5678'));
        $this->assertSame('+201012345678', PhoneNumber::normalize('00201012345678'));
        $this->assertSame('+966501234567', PhoneNumber::normalize('+966 50 123 4567'));
        $this->assertNull(PhoneNumber::normalize('123@g.us'));
        $this->assertNull(PhoneNumber::normalize(null));
    }

    public function test_database_queue_executes_serialized_job_through_bound_sender(): void
    {
        config(['queue.default' => 'database']);
        $user = $this->userWithReminder();
        $sender = Mockery::mock(WhatsAppSender::class);
        $sender->shouldReceive('send')->once()->with(Mockery::type('string'), $user->phone, Mockery::type('string'))->andReturn('sent');
        $this->app->instance(WhatsAppSender::class, $sender);
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('jobs', 1);
        $this->artisan('queue:work', ['--once' => true, '--queue' => 'gym-reminders', '--tries' => 1])->assertSuccessful();
        $this->assertDatabaseCount('jobs', 0);
        $this->assertSame('sent', GymReminderDelivery::sole()->status);
    }

    public function test_disabled_scheduler_stays_quiet_and_expired_outbox_is_not_requeued(): void
    {
        $this->userWithReminder();
        Queue::fake();
        config(['gym_reminders.enabled' => false]);
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 0);
        config(['gym_reminders.enabled' => true]);
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        Queue::fake();
        $this->travel(11)->minutes();
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        Queue::assertNothingPushed();
        $this->assertSame('skipped', GymReminderDelivery::sole()->status);
    }

    public function test_http_adapter_auth_payload_and_conservative_retry_policy(): void
    {
        config(['gym_reminders.provider_token' => str_repeat('a', 32), 'gym_reminders.provider_url' => 'http://provider.test']);
        Http::fake(['provider.test/*' => Http::sequence()
            ->push(['status' => 'sent'])
            ->push(['status' => 'not_ready'], 503)
            ->push(['status' => 'unknown'], 503)
            ->push(['status' => 'unknown'], 409)
            ->push(['status' => 'invalid_recipient'], 422)
            ->pushFailedConnection()]);
        $sender = new WebWhatsAppSender;
        foreach (['sent', 'retry', 'unknown', 'unknown', 'failed', 'unknown'] as $expected) {
            $this->assertSame($expected, $sender->send('key', '+201012345678', 'gym'));
        }
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer '.str_repeat('a', 32))
            && $request['phone'] === '+201012345678' && $request['idempotency_key'] === 'key');
        Http::assertSentCount(6);
    }
}
