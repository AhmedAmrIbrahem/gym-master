<?php

namespace App\Services\GymReminders;

use Illuminate\Support\Facades\Http;
use Throwable;

final class WhatsAppProviderAdmin
{
    public function status(): array
    {
        $data = $this->request('GET', '/session');
        if (! $data || ! in_array($data['status'] ?? '', ['idle', 'starting', 'qr', 'ready', 'disconnected', 'auth_failure', 'error', 'disconnecting'], true)) {
            return ['status' => 'unavailable', 'sender_phone' => null, 'qr_data_url' => null];
        }

        $phone = $data['sender_phone'] ?? null;
        $qr = $data['qr_data_url'] ?? null;

        return [
            'status' => $data['status'],
            'sender_phone' => $data['status'] === 'ready' && is_string($phone) && preg_match('/^\+[1-9][0-9]{7,14}$/D', $phone) ? $phone : null,
            'qr_data_url' => $data['status'] === 'qr' && is_string($qr) && strlen($qr) <= 100000
                && preg_match('/^data:image\/png;base64,[A-Za-z0-9+\/=]+$/D', $qr) ? $qr : null,
        ];
    }

    public function connect(): bool
    {
        return $this->request('POST', '/session/connect') !== null;
    }

    public function disconnect(): bool
    {
        return $this->request('POST', '/session/disconnect') !== null;
    }

    private function request(string $method, string $path): ?array
    {
        $token = (string) config('gym_reminders.admin_token');
        if (strlen($token) < 32 || hash_equals((string) config('gym_reminders.provider_token'), $token)) {
            return null;
        }
        try {
            // Separate management credential: never pass it or provider responses to ordinary users.
            $http = Http::withToken($token)->acceptJson()->connectTimeout(3)->timeout(12);
            $url = rtrim(config('gym_reminders.provider_url'), '/').$path;
            $response = $method === 'GET' ? $http->get($url) : $http->post($url, []);
            $data = $response->json();

            return $response->successful() && is_array($data) ? $data : null;
        } catch (Throwable) {
            return null;
        }
    }
}
