<?php

namespace Tests\Feature;

use App\Models\DailyMeal;
use App\Models\Exercise;
use App\Models\Food;
use App\Models\User;
use App\Models\WorkoutProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private function members(): array
    {
        $one = User::factory()->create();
        $two = User::factory()->create();

        return [$one, $two];
    }

    public function test_member_cannot_modify_another_members_program_or_day(): void
    {
        [$owner, $other] = $this->members();
        $program = WorkoutProgram::create(['user_id' => $owner->id, 'name' => 'Owned', 'is_active' => false]);
        $day = $program->days()->create(['name' => 'Day 1']);
        $this->actingAs($other)->put(route('programs.update', $program), ['name' => 'Stolen'])->assertForbidden();
        $this->actingAs($other)->post(route('programs.activate', $program))->assertForbidden();
        $this->actingAs($other)->delete(route('days.destroy', $day))->assertForbidden();
        $this->assertDatabaseHas('workout_programs', ['id' => $program->id, 'name' => 'Owned']);
    }

    public function test_member_cannot_change_another_members_exercise(): void
    {
        [$owner, $other] = $this->members();
        $exercise = Exercise::create(['user_id' => $owner->id, 'name' => 'Secret', 'target_muscle' => 'Chest']);
        $this->actingAs($other)->delete(route('exercises.destroy', $exercise))->assertForbidden();
        $this->actingAs($other)->put(route('exercises.update', $exercise), ['name' => 'Changed', 'target_muscle' => 'Chest'])->assertForbidden();
    }

    public function test_member_cannot_add_items_to_another_members_meal(): void
    {
        [$owner, $other] = $this->members();
        $meal = DailyMeal::create(['user_id' => $owner->id, 'meal_type' => 'إفطار', 'date' => now()->toDateString()]);
        $food = Food::create(['user_id' => null, 'name' => 'Rice', 'calories_per_100g' => 100, 'protein_per_100g' => 1, 'carbs_per_100g' => 20, 'fats_per_100g' => 1]);
        $this->actingAs($other)->post(route('nutrition.storeMealItem'), ['daily_meal_id' => $meal->id, 'food_id' => $food->id, 'grams' => 100])->assertForbidden();
    }
}
