<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberAreaAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_cannot_open_any_member_screen_even_by_direct_link(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        foreach (['/dashboard', '/nutrition', '/nutrition/history', '/history', '/measurements', '/exercises', '/programs'] as $path) {
            $this->get($path)->assertForbidden();
        }
        foreach (['/nutrition/foods/search?q=rice', '/programs/exercises/search?q=press'] as $path) {
            $this->getJson($path)->assertForbidden();
        }
    }

    public function test_admin_cannot_create_member_records_or_bypass_role_by_posting_forged_user_id(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $member = User::factory()->create(['role' => 'user']);
        $this->actingAs($admin);
        foreach (['/nutrition/food', '/nutrition/item', '/exercises', '/programs', '/measurements', '/workout/store', '/workout/store-single'] as $path) {
            $this->postJson($path, ['user_id' => $member->id, 'role' => 'user'])->assertForbidden();
        }
        foreach (['foods', 'meal_items', 'workout_programs', 'body_measurements', 'workout_sessions'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_member_keeps_access_to_their_pages_but_not_admin_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']));
        foreach (['/dashboard', '/nutrition', '/nutrition/history', '/history', '/measurements', '/exercises', '/programs'] as $path) {
            $this->get($path)->assertOk();
        }
        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/whatsapp')->assertForbidden();
    }

    public function test_existing_owned_models_do_not_allow_admin_to_bypass_the_member_boundary(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $program = WorkoutProgram::create(['user_id' => $admin->id, 'name' => 'Legacy personal program']);
        $day = $program->days()->create(['name' => 'Legacy day']);
        $exercise = Exercise::create(['user_id' => $admin->id, 'name' => 'Legacy exercise', 'target_muscle' => 'Chest']);
        $this->actingAs($admin);
        $this->get(route('workout.start', $day))->assertForbidden();
        $this->put(route('days.update', $day), ['name' => 'Changed'])->assertForbidden();
        $this->put(route('exercises.update', $exercise), ['name' => 'Changed', 'target_muscle' => 'Back'])->assertForbidden();
        $this->delete(route('programs.destroy', $program))->assertForbidden();
        $this->assertDatabaseHas('workout_programs', ['id' => $program->id]);
        $this->assertDatabaseHas('workout_days', ['id' => $day->id, 'name' => 'Legacy day']);
        $this->assertDatabaseHas('exercises', ['id' => $exercise->id, 'name' => 'Legacy exercise']);
    }

    public function test_guest_must_log_in_and_shared_account_settings_remain_available_to_admin(): void
    {
        $this->get('/nutrition/history')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->get('/admin/dashboard')->assertOk();
        $this->get('/profile')->assertOk();
    }
}
