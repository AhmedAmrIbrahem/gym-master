<?php

namespace Tests\Feature;

use App\Models\DailyMeal;
use App\Models\Exercise;
use App\Models\Food;
use App\Models\User;
use App\Models\WorkoutProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_activating_a_program_deactivates_the_users_other_programs_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $old = WorkoutProgram::create(['user_id' => $user->id, 'name' => 'Old', 'is_active' => true]);
        $new = WorkoutProgram::create(['user_id' => $user->id, 'name' => 'New', 'is_active' => false]);
        $foreign = WorkoutProgram::create(['user_id' => $other->id, 'name' => 'Other', 'is_active' => true]);

        $this->actingAs($user)->post(route('programs.activate', $new))->assertRedirect();

        $this->assertFalse((bool) $old->fresh()->is_active);
        $this->assertTrue((bool) $new->fresh()->is_active);
        $this->assertTrue((bool) $foreign->fresh()->is_active);
    }

    public function test_only_accessible_exercises_can_be_attached_to_owned_days(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $day = WorkoutProgram::create(['user_id' => $user->id, 'name' => 'Plan', 'is_active' => true])
            ->days()->create(['name' => 'Day']);
        $foreignExercise = Exercise::create(['user_id' => $other->id, 'name' => 'Private', 'target_muscle' => 'Chest']);
        $ownExercise = Exercise::create(['user_id' => $user->id, 'name' => 'Own', 'target_muscle' => 'Chest']);
        $payload = ['exercise_id' => $foreignExercise->id, 'default_sets' => 3, 'default_reps' => 10];

        $this->actingAs($user)->post(route('days.exercises.attach', $day), $payload)->assertForbidden();
        $this->assertDatabaseMissing('day_exercises', ['workout_day_id' => $day->id, 'exercise_id' => $foreignExercise->id]);
        $this->post(route('days.exercises.attach', $day), [...$payload, 'exercise_id' => $ownExercise->id])->assertRedirect();
        $this->assertDatabaseHas('day_exercises', ['workout_day_id' => $day->id, 'exercise_id' => $ownExercise->id]);
    }

    public function test_meal_item_action_rejects_another_users_food(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $meal = DailyMeal::create(['user_id' => $user->id, 'date' => now()->toDateString(), 'meal_type' => 'إفطار']);
        $food = Food::create(['user_id' => $other->id, 'name' => 'Private', 'calories_per_100g' => 100, 'protein_per_100g' => 1, 'carbs_per_100g' => 2, 'fats_per_100g' => 3]);

        $this->actingAs($user)->post(route('nutrition.storeMealItem'), [
            'daily_meal_id' => $meal->id, 'food_id' => $food->id, 'grams' => 100,
        ])->assertForbidden();
        $this->assertDatabaseMissing('meal_items', ['daily_meal_id' => $meal->id, 'food_id' => $food->id]);
    }
}
