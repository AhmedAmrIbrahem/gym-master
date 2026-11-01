<?php

namespace App\Services\GymReminders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class WebWhatsAppSender implements WhatsAppSender
{
    public function send(string $key, string $phone, string $text): string
    {
        if (strlen((string) config('gym_reminders.provider_token')) < 32) {
            return 'failed';
        }

        try {
            // No automatic HTTP retries: a timeout may follow a successful WhatsApp send.
            $response = Http::withToken(config('gym_reminders.provider_token'))
                ->acceptJson()->connectTimeout(3)->timeout(45)
                ->post(rtrim(config('gym_reminders.provider_url'), '/').'/messages', [
                    'idempotency_key' => $key, 'phone' => $phone, 'text' => $text,
                ]);

            if ($response->successful() && $response->json('status') === 'sent') {
                return 'sent';
            }
            if ($response->status() === 503 && $response->json('status') === 'not_ready') {
                return 'retry';
            }
            if ($response->status() === 422 && $response->json('status') === 'invalid_recipient') {
                return 'failed';
            }

            $state = $response->json('status');
            Log::warning('Gym WhatsApp send not confirmed', [
                'http_status' => $response->status(),
                'provider_status' => in_array($state, ['unknown', 'unauthorized', 'invalid_request', 'key_conflict', 'not_ready'], true) ? $state : 'unexpected',
            ]);

            return 'unknown';
        } catch (Throwable $error) {
            Log::warning('Gym WhatsApp transport not confirmed', ['exception_type' => get_class($error)]);

            return 'unknown';
        }
    }
}
