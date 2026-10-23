<?php

namespace Tests\Feature;

use App\Models\DailyMeal;
use App\Models\Exercise;
use App\Models\Food;
use App\Models\MealItem;
use App\Models\User;
use App\Models\WorkoutProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendCleanupPoliciesHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_another_user_cannot_add_a_day_or_attach_to_a_program_day(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $program = WorkoutProgram::create(['user_id' => $owner->id, 'name' => 'Owner plan', 'is_active' => true]);
        $day = $program->days()->create(['name' => 'Day']);
        $exercise = Exercise::create([
            'user_id' => null, 'name' => 'Shared exercise', 'target_muscle' => 'Chest',
        ]);

        $this->actingAs($other)->post(route('programs.days.store', $program), ['name' => 'Hijack'])->assertForbidden();
        $this->actingAs($other)->post(route('days.exercises.attach', $day), [
            'exercise_id' => $exercise->id, 'default_sets' => 3, 'default_reps' => 10,
        ])->assertForbidden();

        $this->assertDatabaseMissing('workout_days', ['workout_program_id' => $program->id, 'name' => 'Hijack']);
        $this->assertDatabaseMissing('day_exercises', ['workout_day_id' => $day->id, 'exercise_id' => $exercise->id]);
    }

    public function test_history_displays_only_the_signed_in_users_days(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $food = Food::create([
            'user_id' => null, 'name' => 'Rice', 'calories_per_100g' => 100,
            'protein_per_100g' => 2, 'carbs_per_100g' => 20, 'fats_per_100g' => 1,
        ]);
        $mine = DailyMeal::create(['user_id' => $user->id, 'date' => now()->toDateString(), 'meal_type' => 'إفطار']);
        $theirs = DailyMeal::create(['user_id' => $other->id, 'date' => now()->subDays(2)->toDateString(), 'meal_type' => 'غداء']);
        MealItem::create(['daily_meal_id' => $mine->id, 'food_id' => $food->id, 'grams' => 100]);
        MealItem::create(['daily_meal_id' => $theirs->id, 'food_id' => $food->id, 'grams' => 100]);

        $this->actingAs($user)->get(route('nutrition.history'))
            ->assertOk()->assertViewHas('daysLoggedThisMonth', 1)
            ->assertViewHas('paginatedDates', function ($dates) use ($mine) {
                return $dates->total() === 1 && $dates->first()->contains('id', $mine->id);
            });
    }
}
