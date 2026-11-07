<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberNutritionAndPhoneFixTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_member_has_no_preset_nutrition_targets(): void
    {
        $user = User::factory()->create();
        foreach (['target_calories', 'target_protein', 'target_carbs', 'target_fats'] as $field) {
            $this->assertNull($user->fresh()->{$field}, $field);
        }
    }

    public function test_existing_targets_remain_unchanged(): void
    {
        $user = User::factory()->create(['target_calories' => 2400, 'target_protein' => 165]);
        $this->assertEquals(2400, $user->fresh()->target_calories);
        $this->assertEquals(165, $user->fresh()->target_protein);
    }

    public function test_phone_of_new_member_can_be_used_to_sign_in(): void
    {
        $user = User::factory()->create(['phone' => '01018283280']);
        $this->post(route('login'), ['email' => '01018283280', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_equivalent_phone_format_cannot_be_added_by_admin(): void
    {
        User::factory()->create(['phone' => '01018283280']);
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Member Two', 'email' => 'another@example.test',
            'phone' => '+201018283280', 'password' => 'Securepass123!',
            'password_confirmation' => 'Securepass123!',
        ])->assertSessionHasErrors('phone');
    }
}
