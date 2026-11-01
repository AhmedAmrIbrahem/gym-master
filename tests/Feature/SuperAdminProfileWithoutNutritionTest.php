<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminProfileWithoutNutritionTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_profile_hides_nutrition_goals_and_saves_without_them(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
            'target_calories' => 2300,
            'target_protein' => 155,
        ]);

        $this->actingAs($admin)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('تعديل البيانات الشخصية')
            ->assertDontSee('أهداف السعرات والماكروز')
            ->assertDontSee('عضويتك منذ')
            ->assertDontSee('إجمالي الجلسات')
            ->assertDontSee('النظام الحالي')
            ->assertDontSee('name="target_calories"', false)
            ->assertDontSee('name="rest_target_calories"', false);

        $this->actingAs($admin)->patch(route('profile.update'), [
            'name' => 'Gym Admin',
            'email' => $admin->email,
            'target_calories' => 8000, // لا يتم اعتماد الأهداف المرسلة يدويًا للمدير.
            'target_protein' => 999,
        ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();

        $admin->refresh();
        $this->assertSame('Gym Admin', $admin->name);
        $this->assertEquals(2300, $admin->target_calories);
        $this->assertEquals(155, $admin->target_protein);
    }

    public function test_member_still_sees_nutrition_goals_and_requires_them(): void
    {
        $member = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $this->actingAs($member)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('أهداف السعرات والماكروز')
            ->assertSee('عضويتك منذ')
            ->assertSee('إجمالي الجلسات')
            ->assertSee('النظام الحالي')
            ->assertSee('name="target_calories"', false);

        $this->actingAs($member)->patch(route('profile.update'), [
            'name' => 'Member',
            'email' => $member->email,
        ])->assertSessionHasErrors([
            'target_calories', 'target_protein', 'target_carbs', 'target_fats',
        ]);
    }
}
