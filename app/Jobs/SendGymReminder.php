<?php

namespace App\Jobs;

use App\Models\GymReminder;
use App\Models\GymReminderDelivery;
use App\Models\User;
use App\Services\GymReminders\GymReminderSchedule;
use App\Services\GymReminders\PhoneNumber;
use App\Services\GymReminders\ReminderMessageTemplate;
use App\Services\GymReminders\WhatsAppSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SendGymReminder implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 60;

    public int $deliveryId;

    public function __construct(int $deliveryId)
    {
        $this->deliveryId = $deliveryId;
    }

    public function handle(WhatsAppSender $sender): void
    {
        $payload = DB::transaction(function () {
            $ownerId = GymReminderDelivery::whereKey($this->deliveryId)->value('user_id');
            if (! $ownerId) {
                return null;
            }
            // Consistent lock order with dispatcher/settings; serialize schedule eligibility checks.
            User::whereKey($ownerId)->lockForUpdate()->first();
            $delivery = GymReminderDelivery::lockForUpdate()->find($this->deliveryId);
            if (! $delivery || $delivery->status !== 'pending') {
                return null;
            }
            if ($delivery->scheduled_at->isAfter(now('UTC'))) {
                return null;
            }
            $reminder = GymReminder::where('user_id', $delivery->user_id)->first();
            $user = User::find($delivery->user_id);
            $phone = PhoneNumber::normalize($user?->phone);
            if (! config('gym_reminders.enabled') || ! $user?->is_active || $user?->role !== 'user' || ! $reminder?->enabled
                || ! $reminder->consented_at || ! $phone || $phone !== $reminder->consent_phone
                || $reminder->version !== $delivery->settings_version
                || $delivery->scheduled_at->addMinutes(config('gym_reminders.grace_minutes'))->isBefore(now())) {
                $delivery->update(['status' => 'skipped', 'reason' => 'expired_or_settings_changed']);

                return null;
            }
            $gymAt = $delivery->gym_at ?? $delivery->scheduled_at;
            $date = $gymAt->setTimezone(GymReminder::TIMEZONE)->toDateString();
            $expected = GymReminderSchedule::gymAt($reminder, $date);
            if (! $expected || ! $expected->equalTo($gymAt)
                || ! in_array($delivery->offset_minutes, $reminder->offsets(), true)
                || ! $gymAt->subMinutes($delivery->offset_minutes)->equalTo($delivery->scheduled_at)) {
                $delivery->update(['status' => 'skipped', 'reason' => 'day_or_time_changed']);

                return null;
            }
            $text = $delivery->message_text ?? ReminderMessageTemplate::render($user, $gymAt, $delivery->offset_minutes);
            $validLength = ReminderMessageTemplate::fitsProvider($text);
            if (! $validLength || trim($text) === '') {
                $delivery->update(['status' => 'failed', 'reason' => 'invalid_message_length']);

                return null;
            }
            // Snapshot text before sending: provider retries keep the identical payload/key.
            $delivery->update(['status' => 'sending', 'message_text' => $text]);

            return [$delivery->idempotency_key, $phone, $text];
        });

        if (! $payload) {
            return;
        }
        $result = $sender->send(...$payload);
        GymReminderDelivery::whereKey($this->deliveryId)->where('status', 'sending')->update([
            'status' => match ($result) {
                'sent' => 'sent', 'retry' => 'pending', 'failed' => 'failed', default => 'unknown',
            },
            'reason' => $result === 'sent' ? null : 'provider_'.$result,
            'sent_at' => $result === 'sent' ? now() : null,
        ]);
    }
}
