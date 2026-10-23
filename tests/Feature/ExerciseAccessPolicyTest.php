<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExerciseAccessPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_own_and_shared_exercises_are_listed(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Exercise::create(['user_id' => null, 'name' => 'Shared public exercise', 'target_muscle' => 'Chest']);
        Exercise::create(['user_id' => $user->id, 'name' => 'My private exercise', 'target_muscle' => 'Back']);
        Exercise::create(['user_id' => $other->id, 'name' => 'Other private exercise', 'target_muscle' => 'Arms']);

        $this->actingAs($user)->get(route('exercises.index'))
            ->assertOk()
            ->assertSee('Shared public exercise')
            ->assertSee('My private exercise')
            ->assertDontSee('Other private exercise');
    }

    public function test_other_users_and_shared_exercises_cannot_be_changed(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $foreign = Exercise::create(['user_id' => $other->id, 'name' => 'Other', 'target_muscle' => 'Chest']);
        $shared = Exercise::create(['user_id' => null, 'name' => 'Shared', 'target_muscle' => 'Chest']);

        foreach ([$foreign, $shared] as $exercise) {
            $this->actingAs($user)->put(route('exercises.update', $exercise), [
                'name' => 'Unauthorized', 'target_muscle' => 'Chest',
            ])->assertForbidden();
            $this->delete(route('exercises.destroy', $exercise))->assertForbidden();
            $this->assertDatabaseHas('exercises', ['id' => $exercise->id, 'name' => $exercise->name]);
        }
    }

    public function test_owner_can_update_and_delete_own_exercise(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::create(['user_id' => $user->id, 'name' => 'Old', 'target_muscle' => 'Chest']);

        $this->actingAs($user)->put(route('exercises.update', $exercise), [
            'name' => 'Updated', 'target_muscle' => 'Back',
        ])->assertRedirect();
        $this->assertDatabaseHas('exercises', ['id' => $exercise->id, 'name' => 'Updated']);

        $this->delete(route('exercises.destroy', $exercise))->assertRedirect();
        $this->assertDatabaseMissing('exercises', ['id' => $exercise->id]);
    }
}
