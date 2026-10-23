<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminWhatsAppCredentialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_member_credentials_are_available_for_one_redirect_and_email_is_sent(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Test Member',
            'email' => 'testmember@example.com',
            'phone' => '01012345678',
            'password' => 'ExamplePassword123!',
            'password_confirmation' => 'ExamplePassword123!',
        ]);

        $member = User::where('email', 'testmember@example.com')->firstOrFail();
        $response->assertSessionHas('new_member_credentials', function (array $data) use ($member): bool {
            return $data['id'] === $member->id
                && $data['phone'] === $member->phone
                && $data['password'] === 'ExamplePassword123!';
        });
        $this->assertTrue(Hash::check('ExamplePassword123!', $member->password));
        Mail::assertSent(\App\Mail\WelcomeUserMail::class);
    }

    public function test_admin_password_reset_makes_only_the_new_password_available_for_whatsapp(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)->post(route('admin.users.reset-password', $member), [
            'password' => 'AnotherPassword123!',
            'password_confirmation' => 'AnotherPassword123!',
        ])->assertSessionHas('new_member_credentials', function (array $data) use ($member): bool {
            return $data['id'] === $member->id
                && $data['password'] === 'AnotherPassword123!';
        });

        $this->assertTrue(Hash::check('AnotherPassword123!', $member->fresh()->password));
    }
}
