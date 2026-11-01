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
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class PersonalReminderOffsetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['gym_reminders.enabled' => true]);
        Queue::fake();
        $this->travelTo(CarbonImmutable::parse('2026-11-02 14:00', 'Africa/Cairo'));
        $this->actingAs(User::factory()->create(['phone' => '+201012345678']));
    }

    private function payload(string $time = '17:00', array $offsets = [60, 15, 0], int $day = 1): array
    {
        $days = [];
        foreach (range(0, 6) as $d) {
            $days[$d] = ['enabled' => $day === $d ? 1 : 0, 'time' => $time];
        }

        return ['enabled' => 1, 'opt_in' => 1, 'days' => $days, 'reminder_offsets' => $offsets];
    }

    private function save(array $data): void
    {
        $this->put(route('profile.gym-reminder.update'), $data)->assertSessionHasNoErrors();
    }

    private function dispatchAt(string $time, string $zone = 'Africa/Cairo'): void
    {
        $this->travelTo(CarbonImmutable::parse($time, $zone));
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
    }

    public function test_three_distinct_reminders_are_sent_once_and_template_uses_gym_time(): void
    {
        $this->save($this->payload());
        GymReminderMessage::create(['id' => 1, 'text' => 'الجيم {time} يوم {day} | قبلها {minutes_before} دقيقة']);
        $sender = Mockery::mock(WhatsAppSender::class);
        $sender->shouldReceive('send')->once()->with(Mockery::type('string'), '+201012345678', 'الجيم 17:00 يوم الاثنين | قبلها 60 دقيقة')->andReturn('sent');
        $sender->shouldReceive('send')->once()->with(Mockery::type('string'), '+201012345678', 'الجيم 17:00 يوم الاثنين | قبلها 15 دقيقة')->andReturn('sent');
        $sender->shouldReceive('send')->once()->with(Mockery::type('string'), '+201012345678', 'الجيم 17:00 يوم الاثنين | قبلها 0 دقيقة')->andReturn('sent');
        foreach (['16:00', '16:45', '17:00'] as $time) {
            $this->dispatchAt('2026-11-02 '.$time);
            $delivery = GymReminderDelivery::where('status', 'pending')->sole();
            (new SendGymReminder($delivery->id))->handle($sender);
            (new SendGymReminder($delivery->id))->handle($sender);
            $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        }
        $this->assertDatabaseCount('gym_reminder_deliveries', 3);
        $this->assertEquals(3, GymReminderDelivery::where('status', 'sent')->count());
        $this->assertSame([60, 15, 0], GymReminder::sole()->offsets());
    }

    public function test_fourth_duplicate_negative_and_too_large_offsets_are_rejected(): void
    {
        foreach ([[60, 30, 15, 0], [15, 15], [-1], [1441], [], ['bad']] as $offsets) {
            $this->put(route('profile.gym-reminder.update'), $this->payload(offsets: $offsets))->assertSessionHasErrorsIn('gymReminder');
        }
        $this->assertDatabaseCount('gym_reminders', 0);
    }

    public function test_three_attempt_cap_survives_changes_to_gym_time_and_offsets_and_unknown_never_replays(): void
    {
        $sender = Mockery::mock(WhatsAppSender::class);
        $sender->shouldReceive('send')->times(3)->andReturn('unknown');
        foreach (['16:00', '17:00', '18:00', '19:00'] as $time) {
            $this->save($this->payload($time, [0]));
            $this->dispatchAt('2026-11-02 '.$time);
            foreach (GymReminderDelivery::where('status', 'pending')->get() as $delivery) {
                (new SendGymReminder($delivery->id))->handle($sender);
            }
        }
        $this->assertDatabaseCount('gym_reminder_deliveries', 3);
        $this->assertEquals(3, GymReminderDelivery::where('status', 'unknown')->count());
    }

    public function test_same_gym_time_does_not_replay_after_other_settings_change(): void
    {
        $this->save($this->payload(offsets: [60]));
        $this->dispatchAt('2026-11-02 16:00');
        GymReminderDelivery::sole()->update(['status' => 'sent']);
        $this->save($this->payload(offsets: [60, 15]));
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 1);
        $this->dispatchAt('2026-11-02 16:45');
        $this->assertDatabaseCount('gym_reminder_deliveries', 2);
    }

    public function test_midnight_lead_belongs_to_next_gym_day_and_does_not_enable_day_off(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-01 21:00', 'Africa/Cairo'));
        $this->save($this->payload('00:15', [30], 1));
        $this->dispatchAt('2026-11-01 23:45');
        $delivery = GymReminderDelivery::sole();
        $this->assertSame('2026-11-02', $delivery->local_date);
        $this->assertSame('2026-11-01 23:45', $delivery->scheduled_at->setTimezone('Africa/Cairo')->format('Y-m-d H:i'));
        $this->assertSame('2026-11-02 00:15', $delivery->gym_at->setTimezone('Africa/Cairo')->format('Y-m-d H:i'));
        $sender = Mockery::mock(WhatsAppSender::class);
        $sender->shouldReceive('send')->once()->andReturn('sent');
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->dispatchAt('2026-11-02 23:45');
        $this->assertDatabaseCount('gym_reminder_deliveries', 1);
    }

    public function test_elapsed_leads_are_skipped_after_save_but_remaining_future_leads_send(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-02 16:30', 'Africa/Cairo'));
        $this->save($this->payload());
        $this->artisan('gym-reminders:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('gym_reminder_deliveries', 0);
        $this->dispatchAt('2026-11-02 16:45');
        $this->dispatchAt('2026-11-02 17:00');
        $this->assertDatabaseCount('gym_reminder_deliveries', 2);
        $this->assertSame([15, 0], GymReminderDelivery::orderBy('id')->pluck('offset_minutes')->all());
    }

    public function test_reordering_offsets_is_a_noop_and_job_cannot_send_early_or_after_offsets_change(): void
    {
        $this->save($this->payload());
        $version = GymReminder::sole()->version;
        $this->save($this->payload(offsets: [0, 60, 15]));
        $this->assertSame($version, GymReminder::sole()->version);
        $this->dispatchAt('2026-11-02 16:00');
        $delivery = GymReminderDelivery::sole();
        $this->travelTo(CarbonImmutable::parse('2026-11-02 15:59', 'Africa/Cairo'));
        $sender = Mockery::mock(WhatsAppSender::class)->shouldNotReceive('send')->getMock();
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('pending', $delivery->fresh()->status);
        $this->save($this->payload(offsets: [15]));
        $this->travelTo(CarbonImmutable::parse('2026-11-02 16:00', 'Africa/Cairo'));
        (new SendGymReminder($delivery->id))->handle($sender);
        $this->assertSame('skipped', $delivery->fresh()->status);
    }

    public function test_cairo_summer_lead_and_spring_nonexistent_time(): void
    {
        $this->travelTo(CarbonImmutable::parse('2027-07-05 15:00', 'Africa/Cairo'));
        $this->save($this->payload('18:00', [60]));
        $this->dispatchAt('2027-07-05 14:00', 'UTC');
        $this->assertSame('2027-07-05 14:00', GymReminderDelivery::sole()->scheduled_at->utc()->format('Y-m-d H:i'));
        $this->travelTo(CarbonImmutable::parse('2027-04-29 18:00', 'Africa/Cairo'));
        $this->save($this->payload('00:30', [0], 5));
        $this->dispatchAt('2027-04-29 22:30', 'UTC');
        $this->assertDatabaseCount('gym_reminder_deliveries', 1);
    }

    public function test_admin_can_use_lead_variable_and_member_cannot_override_template(): void
    {
        $this->put(route('admin.whatsapp.message.update'), ['text' => 'موعدك {time} بعد {minutes_before} دقيقة'])->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->put(route('admin.whatsapp.message.update'), ['text' => 'موعدك {time} بعد {minutes_before} دقيقة'])->assertSessionHasNoErrors();
        $this->assertSame('موعدك {time} بعد {minutes_before} دقيقة', GymReminderMessage::sole()->text);
    }
}
