<?php

namespace App\Services\GymReminders;

final class PhoneNumber
{
    public static function normalize(?string $phone): ?string
    {
        $phone = preg_replace('/[\s().-]+/', '', $phone ?? '');
        if (str_starts_with($phone, '00')) {
            $phone = '+'.substr($phone, 2);
        }
        if (config('gym_reminders.egypt_local_numbers') && preg_match('/^01[0125][0-9]{8}$/D', $phone)) {
            $phone = '+20'.substr($phone, 1);
        }

        return preg_match('/^\+[1-9][0-9]{7,14}$/D', $phone) ? $phone : null;
    }
}
