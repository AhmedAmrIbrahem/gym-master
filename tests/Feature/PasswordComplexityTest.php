<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordComplexityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_password_requires_capitals_lowercase_and_numbers(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        foreach (['onlylowercase' => 'mixed', 'ONLYUPPERCASE1' => 'mixed', 'NoNumbers' => 'numbers'] as $password => $rule) {
            $this->actingAs($admin)->post(route('admin.users.store'), [
                'name' => 'مستخدم', 'email' => 'check@example.com', 'phone' => '01012345678',
                'password' => $password, 'password_confirmation' => $password,
            ])->assertSessionHasErrors('password');
        }
        $this->assertDatabaseMissing('users', ['email' => 'check@example.com']);
    }

    public function test_admin_password_confirmation_must_match(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'مستخدم', 'email' => 'check@example.com', 'phone' => '01012345678',
            'password' => 'SecurePass123', 'password_confirmation' => 'OtherPass123',
        ])->assertSessionHasErrors('password');
    }
}
