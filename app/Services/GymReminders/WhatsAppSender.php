<?php

namespace App\Services\GymReminders;

interface WhatsAppSender
{
    /** Returns sent, retry (definitely not sent), failed, or unknown. Never blindly retry uncertainty. */
    public function send(string $key, string $phone, string $text): string;
}
