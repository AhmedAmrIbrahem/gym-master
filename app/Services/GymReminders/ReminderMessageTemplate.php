<?php

namespace App\Services\GymReminders;

use App\Models\GymReminder;
use App\Models\GymReminderMessage;
use App\Models\User;
use Carbon\CarbonImmutable;

class ReminderMessageTemplate
{
    public const DEFAULT_TEXT = 'أهلًا {name}، تذكير بموعد الجيم يوم {day} الساعة {time} بتوقيت القاهرة. يمكنك إيقاف التذكير من إعدادات حسابك.';

    public static function current(): string
    {
        return GymReminderMessage::find(1)?->text ?? self::DEFAULT_TEXT;
    }

    public static function values(string $name, CarbonImmutable $scheduled): array
    {
        $local = $scheduled->setTimezone(GymReminder::TIMEZONE);

        return ['{name}' => $name, '{time}' => $local->format('H:i'), '{day}' => GymReminder::DAYS[$local->dayOfWeek], '{date}' => $local->toDateString(), '{timezone}' => 'القاهرة'];
    }

    public static function render(User $user, CarbonImmutable $scheduled): string
    {
        return strtr(self::current(), self::values($user->name, $scheduled));
    }

    public static function fitsProvider(string $text): bool
    {
        return strlen(mb_convert_encoding($text, 'UTF-16LE', 'UTF-8')) <= 3000;
    }
}
