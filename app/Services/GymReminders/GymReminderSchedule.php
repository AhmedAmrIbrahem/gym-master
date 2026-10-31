<?php

namespace App\Services\GymReminders;

use App\Models\GymReminder;
use Carbon\CarbonImmutable;

class GymReminderSchedule
{
    public static function gymAt(GymReminder $reminder, string $date): ?CarbonImmutable
    {
        $day = CarbonImmutable::parse($date.' 12:00', GymReminder::TIMEZONE);
        $time = $reminder->scheduleForWeek()[$day->dayOfWeek] ?? null;
        if (! is_string($time)) {
            return null;
        }
        $local = CarbonImmutable::parse($date.' '.$time, GymReminder::TIMEZONE);

        // Do not silently shift a nonexistent wall-clock time during Cairo's spring DST jump.
        return $local->format('Y-m-d H:i') === $date.' '.$time ? $local->utc() : null;
    }

    public static function key(int $userId, CarbonImmutable $gymAt, int $offset): string
    {
        // Local wall time deduplicates the repeated hour when daylight saving ends.
        return hash('sha256', $userId.'|'.$gymAt->setTimezone(GymReminder::TIMEZONE)->format('Y-m-d H:i').'|'.$offset);
    }
}
