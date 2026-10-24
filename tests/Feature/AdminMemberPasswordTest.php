<?php

namespace Tests\Feature;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminMemberPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_creates_member_emails_credentials_and_shows_them_once(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Member',
            'email' => 'newmember@example.com',
            'phone' => '01012345678',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertSessionHasNoErrors();

        $member = User::where('email', 'newmember@example.com')->firstOrFail();
        $this->assertSame('user', $member->role);
        $this->assertTrue(Hash::check('SecurePass123!', $member->password));
        $this->assertNotSame('SecurePass123!', $member->password);
        Mail::assertSent(WelcomeUserMail::class, function (WelcomeUserMail $mail) use ($member) {
            return $mail->hasTo($member->email)
                && $mail->plainPassword === 'SecurePass123!';
        });
        $this->assertSame('SecurePass123!', session('new_member_credentials.password'));
        $this->assertSame($member->email, session('new_member_credentials.email'));
    }

    public function test_super_admin_can_change_member_password_without_email(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'super_admin']);
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)->post(route('admin.users.reset-password', $member), [
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('NewSecurePass123!', $member->fresh()->password));
        Notification::assertNothingSent();
    }

    public function test_member_cannot_change_another_members_password(): void
    {
        $member = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);
        $hash = $other->password;

        $this->actingAs($member)->post(route('admin.users.reset-password', $other), [
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!',
        ])->assertForbidden();

        $this->assertSame($hash, $other->fresh()->password);
    }

    public function test_super_admin_cannot_reset_another_super_admin_password(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $otherAdmin = User::factory()->create(['role' => 'super_admin']);
        $hash = $otherAdmin->password;

        $this->actingAs($admin)->post(route('admin.users.reset-password', $otherAdmin), [
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!',
        ])->assertForbidden();

        $this->assertSame($hash, $otherAdmin->fresh()->password);
    }

    public function test_password_confirmation_is_required(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $member = User::factory()->create(['role' => 'user']);
        $hash = $member->password;

        $this->actingAs($admin)->post(route('admin.users.reset-password', $member), [
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');

        $this->assertSame($hash, $member->fresh()->password);
    }
}
