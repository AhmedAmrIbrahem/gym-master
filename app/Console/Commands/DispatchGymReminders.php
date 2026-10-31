<?php

namespace App\Console\Commands;

use App\Jobs\SendGymReminder;
use App\Models\GymReminder;
use App\Models\GymReminderDelivery;
use App\Models\User;
use App\Services\GymReminders\GymReminderSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DispatchGymReminders extends Command
{
    protected $signature = 'gym-reminders:dispatch';

    protected $description = 'Queue up to three personal reminders per gym day; recover pending outbox deliveries';

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
                    DB::transaction(function () use ($reminder, $now) {
                        // The same account lock is used by settings saves. Concurrent dispatchers
                        // cannot reserve a fourth slot or race a settings change.
                        $user = User::whereKey($reminder->user_id)->lockForUpdate()->first();
                        $current = GymReminder::find($reminder->id);
                        if (! $current?->enabled || ! $user?->is_active || $user->role !== 'user') {
                            return;
                        }
                        GymReminderDelivery::where('user_id', $user->id)->where('status', 'pending')
                            ->where('settings_version', '!=', $current->version)
                            ->update(['status' => 'skipped', 'reason' => 'settings_changed']);
                        $local = $now->setTimezone(GymReminder::TIMEZONE);
                        // Yesterday covers midnight grace; two days ahead covers a 24h lead
                        // around DST. All subtraction uses UTC elapsed minutes.
                        foreach (range(-1, 2) as $dayOffset) {
                            $date = $local->setTime(12, 0)->addDays($dayOffset)->toDateString();
                            $gymAt = GymReminderSchedule::gymAt($current, $date);
                            if (! $gymAt) {
                                continue;
                            }
                            foreach ($current->offsets() as $offset) {
                                $scheduled = $gymAt->subMinutes($offset);
                                if (($current->eligible_after && $scheduled->lessThanOrEqualTo($current->eligible_after))
                                    || $scheduled->isAfter($now) || $scheduled->addMinutes(config('gym_reminders.grace_minutes'))->isBefore($now)) {
                                    continue;
                                }
                                $key = GymReminderSchedule::key($user->id, $gymAt, $offset);
                                $existing = GymReminderDelivery::where('user_id', $user->id)->where('local_date', $date)
                                    ->where('offset_minutes', $offset)->where(function ($q) use ($key, $gymAt) {
                                        $q->where('occurrence_key', $key)->orWhere('gym_at', $gymAt->format('Y-m-d H:i:s'));
                                    })->first();
                                // A sent/unknown attempt never replays, even after toggling settings.
                                if ($existing && $existing->status !== 'skipped') {
                                    continue;
                                }
                                $reserved = GymReminderDelivery::where('user_id', $user->id)->where('local_date', $date)
                                    ->where('status', '!=', 'skipped')->count();
                                if ($reserved >= 3) {
                                    continue;
                                }
                                $attributes = ['user_id' => $user->id, 'local_date' => $date,
                                    'settings_version' => $current->version, 'occurrence_key' => $key,
                                    'offset_minutes' => $offset, 'gym_at' => $gymAt,
                                    'idempotency_key' => (string) Str::uuid(), 'scheduled_at' => $scheduled,
                                    'status' => 'pending', 'reason' => null, 'message_text' => null];
                                if ($existing) {
                                    $existing->update($attributes);
                                } else {
                                    GymReminderDelivery::create($attributes);
                                }
                            }
                        }
                    });
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
