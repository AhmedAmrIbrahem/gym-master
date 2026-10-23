<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArabicValidationOldInputTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_failure_keeps_email_and_has_arabic_message(): void
    {
        $response = $this->from('/login')->post(route('login'), [
            'email' => 'member@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasInput('email', 'member@example.com')
            ->assertSessionMissing('_old_input.password');
        $this->assertSame('البريد الإلكتروني أو كلمة المرور غير صحيحة.', session('errors')->first('email'));
    }

    public function test_admin_member_form_keeps_safe_fields_without_passwords(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($admin)->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name' => 'محمود',
                'email' => 'mahmoud@example.com',
                'phone' => '01012345678',
                'password' => 'password123',
                'password_confirmation' => 'notmatching',
            ]);

        $response->assertRedirect(route('admin.users.index'))
            ->assertSessionHasInput('name', 'محمود')
            ->assertSessionHasInput('email', 'mahmoud@example.com')
            ->assertSessionHasInput('phone', '01012345678')
            ->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('_old_input.password_confirmation');
    }
}
