<?php

namespace App\Services;

use App\Models\MemberWhatsappCredential;
use App\Models\User;
use App\Services\GymReminders\PhoneNumber;
use App\Services\GymReminders\WhatsAppSender;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

final class MemberWhatsAppCredentials
{
    public function __construct(private readonly WhatsAppSender $sender) {}

    /** The initial password is only recoverable for 24 hours (APP_KEY encryption). */
    public function saveInitialPassword(User $user, string $plainPassword): void
    {
        MemberWhatsappCredential::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'encrypted_password' => Crypt::encryptString($plainPassword),
                'password_expires_at' => now()->addDay(),
                'status' => 'pending',
                'message_type' => null,
                'last_attempt_key' => null,
                'last_attempt_at' => null,
            ],
        );
    }

    /** Return sent, failed, retry, or unknown. Unknown means delivery is not confirmed. */
    public function send(User $user): string
    {
        $record = MemberWhatsappCredential::query()->firstOrCreate(['user_id' => $user->id]);
        $password = null;
        if ($record->encrypted_password !== null && $record->password_expires_at?->isFuture()) {
            try {
                $candidate = Crypt::decryptString($record->encrypted_password);
                // A member may have changed their password since the original send.
                if (Hash::check($candidate, $user->password)) {
                    $password = $candidate;
                }
            } catch (Throwable) {
                // Fall back to a reset link if the encrypted secret cannot be recovered.
            }
        }
        if ($password === null && $record->encrypted_password !== null) {
            $record->encrypted_password = null;
            $record->password_expires_at = null;
        }

        $phone = PhoneNumber::normalize($user->phone);
        $type = $password === null ? 'reset_link' : 'password';
        $key = (string) Str::uuid();
        $record->status = 'sending';
        $record->message_type = $type;
        $record->last_attempt_key = $key;
        $record->last_attempt_at = now();
        $record->save();

        if ($phone === null) {
            $record->update(['status' => 'failed']);

            return 'failed';
        }

        if ($password === null) {
            // Laravel stores only a hash of the reset token, with an expiry defined in config/auth.php.
            $token = Password::broker()->createToken($user);
            $url = route('password.reset', ['token' => $token, 'email' => $user->email]);
            $body = "مرحباً {$user->name}\nبيانات حسابك في Gym Master\n\nالبريد الإلكتروني: {$user->email}\nرقم الهاتف: {$user->phone}\nلتعيين كلمة مرور جديدة افتح الرابط التالي:\n{$url}";
        } else {
            $body = "مرحباً {$user->name}\nبيانات حسابك في Gym Master\n\nالبريد الإلكتروني: {$user->email}\nرقم الهاتف: {$user->phone}\nكلمة المرور: {$password}\n\nرابط تسجيل الدخول:\n".route('login');
        }

        // Provider supports idempotency keys. Do not automatically retry unknown sends.
        try {
            $result = $this->sender->send($key, $phone, $body);
        } catch (Throwable) {
            $result = 'unknown';
        }
        $status = in_array($result, ['sent', 'failed', 'retry', 'unknown'], true) ? $result : 'unknown';
        $record->update(['status' => $status]);

        return $status;
    }
}
