<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use App\Rules\PasswordCharacters;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Keep authentication and validation messages in Arabic.
        $this->app->setLocale('ar');

        Password::defaults(static fn () => Password::min(8)->rules([new PasswordCharacters()]));

        // تعطيل فحص SSL لطلبات cURL الخروجية في البيئة المحلية محلياً
        if ($this->app->environment('local')) {
            Http::macro('geminiClient', function () {
                return Http::withoutVerifying();
            });
        }
    }
}
