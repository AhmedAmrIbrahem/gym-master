<?php

namespace Tests\Feature;

use App\Jobs\SendGymReminder;
use App\Models\GymReminder;
use App\Models\GymReminderDelivery;
use App\Models\GymReminderMessage;
use App\Models\User;
use App\Services\GymReminders\WhatsAppSender;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class WeeklyGymReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['gym_reminders.enabled' => true]);
        $this->travelTo(CarbonImmutable::parse('2026-11-02 17:00:00', 'Africa/Cairo')); // Monday.
        Queue::fake();
        Http::preventStrayRequests();
    }

    private function payload(array $schedule, bool $enabled = true): array
    {
        $days = [];
        foreach (range(0, 6) as $day) {
            $days[$day] = ['enabled' => isset($schedule[$day]) ? 1 : 0, 'time' => $schedule[$day] ?? '18:00'];
        }

        return ['enabled' => $enabled ? 1 : 0, 'days' => $days, ...($enabled ? ['opt_in' => 1] : [])];
    }

    private function member(): User
    {
        $user = User::factory()->create(['name' => 'أحمد', 'phone' => '+201012345678']);
        $this->actingAs($user);

        return $user;
    }

    private function save(array $schedule, bool $enabled = true): void
    {
        $this->put(route('profile.gym-reminder.update'), $this->payload($schedule, $enabled))
            ->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();
    }

    public function test_weekday_times_are_independent_unselected_days_never_send_and_timezone_is_forced(): void
    {
        $user = $this->member();
        $this->put(route('profile.gym-reminder.update'), $this->payload([1 => '18:00', 3 => '20:30']) + ['timezone' => 'America/New_York'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Africa/Cairo', GymReminder::sole()->timezone);
        $this->assertSame([1 => '18:00', 3 => '20:30'], GymReminder::sole()->weekly_schedule);
        $this->get(route('profile.edit'))->assertOk()->assertSee('توقيت مصر')->assertDontSee('name="timezone"', false);
        foreach (['2026-11-02 18:00', '2026-11-03 18:00', '2026-11-04 18:00', '2026-11-04 20:30'] as $date) {
            $this->travelTo(CarbonImmutable::parse($date, 'Africa/Cairo'));
            $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        }
        $this->assertDatabaseCount('gym_reminder_deliveries', 2);
        $this->assertSame(['2026-11-02', '2026-11-04'], GymReminderDelivery::where('user_id', $user->id)->orderBy('id')->pluck('local_date')->all());
        $this->assertSame('20:30', GymReminderDelivery::latest('id')->first()->scheduled_at->setTimezone('Africa/Cairo')->format('H:i'));
    }

    public function test_same_day_new_future_time_sends_after_previous_success_but_same_settings_do_not_repeat(): void
    {
        $this->member();
        $this->save([1 => '17:02']);
        $this->travel(2)->minutes();
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $first = GymReminderDelivery::sole();
        $sender = Mockery::mock(WhatsAppSender::class);
        $sender->shouldReceive('send')->twice()->andReturn('sent');
        (new SendGymReminder($first->id))->handle($sender);
        $version = GymReminder::sole()->version;
        $this->save([1 => '17:02']);
        $this->assertSame($version, GymReminder::sole()->version);
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 1);
        $this->save([1 => '17:05']);
        $this->assertNotSame($version, GymReminder::sole()->version);
        $this->travel(3)->minutes();
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $second = GymReminderDelivery::latest('id')->first();
        $this->assertDatabaseCount('gym_reminder_deliveries', 2);
        $this->assertNotSame($first->idempotency_key, $second->idempotency_key);
        (new SendGymReminder($second->id))->handle($sender);
        (new SendGymReminder($second->id))->handle($sender);
        $this->assertSame(2, GymReminderDelivery::where('status', 'sent')->count());
    }

    public function test_saving_at_or_after_chosen_minute_does_not_send_immediately(): void
    {
        $this->member();
        $this->save([1 => '16:59']);
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 0);
        $this->save([1 => '17:00']);
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 0);
        $this->travelTo(CarbonImmutable::parse('2026-11-09 17:00', 'Africa/Cairo'));
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 1);
    }

    public function test_schedule_change_invalidates_old_queued_job_and_day_off_stays_off(): void
    {
        $this->member();
        $this->save([1 => '17:01', 3 => '19:00']);
        $this->travel(1)->minutes();
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $delivery = GymReminderDelivery::sole();
        $this->save([3 => '19:00']);
        $sender = Mockery::mock(WhatsAppSender::class)->shouldNotReceive('send')->getMock();
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('skipped', $delivery->fresh()->status);
        $this->travel(1)->minutes();
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 1);
    }

    public function test_rejects_empty_enabled_schedule_unknown_day_invalid_time_and_missing_consent(): void
    {
        $this->member();
        $this->put(route('profile.gym-reminder.update'), $this->payload([]))->assertSessionHasErrorsIn('gymReminder', 'days');
        $payload = $this->payload([1 => '18:00']);
        $payload['days'][7] = ['enabled' => 1, 'time' => '19:00'];
        $this->put(route('profile.gym-reminder.update'), $payload)->assertSessionHasErrorsIn('gymReminder', 'days');
        $payload = $this->payload([1 => '99:00']);
        unset($payload['opt_in']);
        $this->put(route('profile.gym-reminder.update'), $payload)->assertSessionHasErrorsIn('gymReminder', ['days.1.time', 'opt_in']);
        $this->assertDatabaseCount('gym_reminders', 0);
        $this->save([], false);
        $this->assertFalse(GymReminder::sole()->enabled);
    }

    public function test_summer_cairo_timezone_uses_dst_instead_of_fixed_utc_offset(): void
    {
        $this->member();
        $this->travelTo(CarbonImmutable::parse('2027-07-05 17:00', 'Africa/Cairo'));
        $this->save([1 => '18:00']);
        $this->travelTo(CarbonImmutable::parse('2027-07-05 15:00', 'UTC'));
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertSame('15:00', GymReminderDelivery::sole()->scheduled_at->utc()->format('H:i'));
    }

    public function test_template_variables_are_personalized_and_message_is_frozen_across_not_ready_retries(): void
    {
        $this->member();
        GymReminderMessage::create(['id' => 1, 'text' => 'أهلًا {name} | {day} | {time} | {date} | {timezone}']);
        $this->save([1 => '17:02']);
        $this->travel(2)->minutes();
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $delivery = GymReminderDelivery::sole();
        $expected = 'أهلًا أحمد | الاثنين | 17:02 | 2026-11-02 | القاهرة';
        $sender = Mockery::mock(WhatsAppSender::class);
        $sender->shouldReceive('send')->twice()->with($delivery->idempotency_key, '+201012345678', $expected)->andReturn('retry', 'sent');
        (new SendGymReminder($delivery->id))->handle($sender);
        GymReminderMessage::find(1)->update(['text' => 'نص جديد {time}']);
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame($expected, $delivery->fresh()->message_text);
        $this->assertSame('sent', $delivery->fresh()->status);
    }

    public function test_subscriber_cannot_change_message_even_when_posting_directly(): void
    {
        $this->member();
        $this->putJson(route('admin.whatsapp.message.update'), ['text' => 'forged'])->assertForbidden();
        $this->assertDatabaseCount('gym_reminder_messages', 0);
        $this->post(route('admin.whatsapp.message.update'), ['text' => 'forged'])->assertStatus(405);
    }

    public function test_super_admin_controls_message_without_modifying_subscriber_schedule_or_replaying_today(): void
    {
        $this->member();
        $this->save([1 => '17:01']);
        $this->travel(1)->minutes();
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        GymReminderDelivery::sole()->update(['status' => 'sent']);
        $version = GymReminder::sole()->version;
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->put(route('admin.whatsapp.message.update'), ['text' => 'يلا يا {name}، الجيم {time} يوم {day}'])
            ->assertRedirect(route('admin.whatsapp.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('gym_reminder_messages', ['id' => 1, 'text' => 'يلا يا {name}، الجيم {time} يوم {day}']);
        $this->assertSame($version, GymReminder::sole()->version);
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 1);
    }

    public function test_invalid_or_overexpanded_admin_template_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        foreach (['', 'مرحبا {password}', str_repeat('{name}', 4), str_repeat('س', 1001)] as $text) {
            $this->put(route('admin.whatsapp.message.update'), ['text' => $text])->assertSessionHasErrorsIn('whatsappMessage', 'text');
        }
        $this->assertDatabaseCount('gym_reminder_messages', 0);
    }
}
