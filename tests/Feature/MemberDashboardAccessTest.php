<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_member_can_open_dashboard_after_login(): void
    {
        $member = User::factory()->unverified()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->post(route('login'), [
            'email' => $member->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($member);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_guest_cannot_open_member_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_unverified_member_cannot_open_admin_dashboard(): void
    {
        $member = User::factory()->unverified()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->actingAs($member)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_unverified_member_can_open_other_member_pages(): void
    {
        $member = User::factory()->unverified()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->actingAs($member)->get(route('programs.index'))->assertOk();
        $this->actingAs($member)->get(route('nutrition.index'))->assertOk();
    }
}
