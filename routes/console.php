<?php

use App\Models\FoodPhotoDraft;
use App\Models\MemberWhatsappCredential;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('gym-reminders:dispatch')->everyMinute()->withoutOverlapping(5);
Schedule::command('model:prune', ['--model' => [FoodPhotoDraft::class]])
    ->dailyAt('03:30')->timezone('Africa/Cairo')->withoutOverlapping(10);

Schedule::call(function (): void {
    MemberWhatsappCredential::query()->whereNotNull('encrypted_password')
        ->where('password_expires_at', '<=', now())
        ->update(['encrypted_password' => null, 'password_expires_at' => null]);
})->name('member-whatsapp-credentials-cleanup')->hourly()->withoutOverlapping(10);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
