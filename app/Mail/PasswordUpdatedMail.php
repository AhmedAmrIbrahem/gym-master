<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $newPassword;

    public function __construct(User $user, string $newPassword)
    {
        $this->user = $user;
        $this->newPassword = $newPassword;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تم تحديث كلمة المرور الخاصة بحسابك - Gym Master',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password_updated',
        );
    }
}
