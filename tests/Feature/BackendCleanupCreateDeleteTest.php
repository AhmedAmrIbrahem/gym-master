<?php

namespace Tests\Feature;

use App\Models\DailyMeal;
use App\Models\Food;
use App\Models\MealItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendCleanupCreateDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_program_is_active_and_second_is_not(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('programs.store'), ['name' => 'First'])->assertRedirect();
        $this->post(route('programs.store'), ['name' => 'Second'])->assertRedirect();

        $this->assertDatabaseHas('workout_programs', ['user_id' => $user->id, 'name' => 'First', 'is_active' => 1]);
        $this->assertDatabaseHas('workout_programs', ['user_id' => $user->id, 'name' => 'Second', 'is_active' => 0]);
    }

    public function test_created_food_belongs_to_logged_in_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('nutrition.storeFood'), [
            'name' => 'Oats', 'calories_per_100g' => 390,
            'protein_per_100g' => 17, 'carbs_per_100g' => 66, 'fats_per_100g' => 7,
            'user_id' => 99999,
        ])->assertRedirect();

        $this->assertDatabaseHas('foods', ['name' => 'Oats', 'user_id' => $user->id]);
    }

    public function test_only_meal_owner_can_delete_an_item(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $food = Food::create([
            'name' => 'Rice', 'calories_per_100g' => 130,
            'protein_per_100g' => 2, 'carbs_per_100g' => 28, 'fats_per_100g' => 0,
        ]);
        $meal = DailyMeal::create([
            'user_id' => $owner->id, 'date' => today()->toDateString(), 'meal_type' => 'إفطار',
        ]);
        $item = MealItem::create(['daily_meal_id' => $meal->id, 'food_id' => $food->id, 'grams' => 100]);

        $this->actingAs($other)->delete(route('nutrition.destroyMealItem', $item))->assertForbidden();
        $this->assertDatabaseHas('meal_items', ['id' => $item->id]);

        $this->actingAs($owner)->delete(route('nutrition.destroyMealItem', $item))->assertRedirect();
        $this->assertDatabaseMissing('meal_items', ['id' => $item->id]);
    }
}
