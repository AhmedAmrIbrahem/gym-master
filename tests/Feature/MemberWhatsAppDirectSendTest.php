<?php

namespace Tests\Feature;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use App\Services\GymReminders\WhatsAppSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MemberWhatsAppDirectSendTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_member_sends_directly_and_resend_reuses_password(): void
    {
        Mail::fake();
        $sender = \Mockery::mock(WhatsAppSender::class);
        $sender->shouldReceive('send')->twice()->withArgs(function ($key, $phone, $message) {
            return preg_match('/^[0-9a-f-]{36}$/', $key)
                && $phone === '+201012345678'
                && str_contains($message, 'SafePassword123!')
                && str_contains($message, 'رقم الهاتف: 01012345678');
        })->andReturn('sent');
        $this->app->instance(WhatsAppSender::class, $sender);

        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Member', 'email' => 'new@example.com', 'phone' => '01012345678',
            'password' => 'SafePassword123!', 'password_confirmation' => 'SafePassword123!',
        ])->assertSessionHasNoErrors();
        Mail::assertSent(WelcomeUserMail::class, 1);
        $member = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertSame('sent', $member->whatsappCredential->status);
        $this->assertNotSame('SafePassword123!', $member->whatsappCredential->encrypted_password);
        $this->actingAs($admin)->post(route('admin.users.resend-whatsapp', $member))->assertSessionHas('success');
    }

    public function test_resend_after_expiry_sends_reset_link_not_old_password(): void
    {
        $sender = \Mockery::mock(WhatsAppSender::class);
        $sender->shouldReceive('send')->once()->withArgs(function ($key, $phone, $message) {
            return $phone === '+201012345678'
                && str_contains($message, '/reset-password/')
                && ! str_contains($message, 'OldPassword123!');
        })->andReturn('sent');
        $this->app->instance(WhatsAppSender::class, $sender);

        $admin = User::factory()->create(['role' => 'super_admin']);
        $member = User::factory()->create(['role' => 'user', 'phone' => '01012345678']);
        $member->whatsappCredential()->create([
            'encrypted_password' => encrypt('OldPassword123!'),
            'password_expires_at' => now()->subMinute(),
            'status' => 'sent',
        ]);
        $this->actingAs($admin)->post(route('admin.users.resend-whatsapp', $member))->assertSessionHas('success');
        $this->assertNull($member->whatsappCredential->fresh()->encrypted_password);
        $this->assertSame('reset_link', $member->whatsappCredential->fresh()->message_type);
    }

    public function test_email_is_attempted_even_if_whatsapp_sender_throws(): void
    {
        Mail::fake();
        $sender = \Mockery::mock(WhatsAppSender::class);
        $sender->shouldReceive('send')->once()->andThrow(new \RuntimeException('provider unavailable'));
        $this->app->instance(WhatsAppSender::class, $sender);

        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Member', 'email' => 'new@example.com', 'phone' => '01012345678',
            'password' => 'SafePassword123!', 'password_confirmation' => 'SafePassword123!',
        ])->assertSessionHasNoErrors();

        Mail::assertSent(WelcomeUserMail::class, 1);
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }
}
