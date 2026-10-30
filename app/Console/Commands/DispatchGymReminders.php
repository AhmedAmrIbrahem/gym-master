<?php

namespace App\Console\Commands;

use App\Jobs\SendGymReminder;
use App\Models\GymReminder;
use App\Models\GymReminderDelivery;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DispatchGymReminders extends Command
{
    protected $signature = 'gym-reminders:dispatch';

    protected $description = 'Queue due daily WhatsApp reminders; recover pending outbox deliveries';

    public function handle(): int
    {
        if (! config('gym_reminders.enabled')) {
            return self::SUCCESS;
        }
        $now = CarbonImmutable::now('UTC');
        GymReminder::where('enabled', true)
            ->whereIn('user_id', User::select('id')->where('role', 'user')->where('is_active', true))
            ->chunkById(200, function ($reminders) use ($now) {
                foreach ($reminders as $reminder) {
                    $local = $now->setTimezone(GymReminder::TIMEZONE);
                    $time = $reminder->scheduleForWeek()[$local->dayOfWeek] ?? null;
                    if ($time === null) {
                        continue;
                    }
                    $scheduled = CarbonImmutable::parse($local->toDateString().' '.$time, GymReminder::TIMEZONE)->utc();
                    if ($reminder->eligible_after && $scheduled->lessThanOrEqualTo($reminder->eligible_after)) {
                        continue;
                    }
                    if ($scheduled->isAfter($now) || $scheduled->addMinutes(config('gym_reminders.grace_minutes'))->isBefore($now)) {
                        continue;
                    }
                    // DB uniqueness also protects against concurrent scheduler processes and DST repeats.
                    GymReminderDelivery::firstOrCreate([
                        'user_id' => $reminder->user_id, 'local_date' => $local->toDateString(),
                        'settings_version' => $reminder->version,
                    ], [
                        'idempotency_key' => (string) Str::uuid(),
                        'scheduled_at' => $scheduled,
                    ]);
                }
            });

        GymReminderDelivery::where('status', 'pending')
            ->where('scheduled_at', '<', $now->subMinutes(config('gym_reminders.grace_minutes'))->format('Y-m-d H:i:s'))
            ->update(['status' => 'skipped', 'reason' => 'expired']);

        GymReminderDelivery::where('status', 'pending')->chunkById(200, function ($deliveries) {
            foreach ($deliveries as $delivery) {
                SendGymReminder::dispatch($delivery->id)->onQueue(config('gym_reminders.queue'));
            }
        });

        return self::SUCCESS;
    }
}
