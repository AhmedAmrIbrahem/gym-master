<?php

return [
    'enabled' => (bool) env('GYM_REMINDERS_ENABLED', false),
    'queue' => 'gym-reminders',
    'grace_minutes' => 10,
    'provider_url' => env('GYM_WHATSAPP_URL', 'http://127.0.0.1:3100'),
    'provider_token' => env('GYM_WHATSAPP_TOKEN', ''),
    'admin_token' => env('GYM_WHATSAPP_ADMIN_TOKEN', ''),
    // Only Egyptian mobile local numbers are expanded; other countries use +country code.
    'egypt_local_numbers' => (bool) env('GYM_WHATSAPP_EGYPT_LOCAL', true),
];
