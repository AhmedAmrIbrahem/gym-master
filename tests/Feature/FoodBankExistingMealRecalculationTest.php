<?php

namespace Tests\Feature;

use App\Models\DailyMeal;
use App\Models\Food;
use App\Models\MealItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodBankExistingMealRecalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_food_changes_macros_of_existing_meal_item(): void
    {
        $user = User::factory()->create();
        $food = Food::create([
            'user_id' => $user->id,
            'name' => 'جبنة قريش',
            'calories_per_100g' => 100,
            'protein_per_100g' => 15,
            'carbs_per_100g' => 0,
            'fats_per_100g' => 4,
        ]);
        $meal = DailyMeal::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'meal_type' => 'إفطار',
        ]);
        $item = MealItem::create([
            'daily_meal_id' => $meal->id,
            'food_id' => $food->id,
            'grams' => 180,
        ]);

        $this->assertEqualsWithDelta(0, $item->fresh()->carbs, 0.01);

        $this->actingAs($user)->put(route('nutrition.updateFood', $food), [
            'name' => 'جبنة قريش',
            'nutrition_reference_grams' => 100,
            'calories_per_100g' => 100,
            'protein_per_100g' => 15,
            'carbs_per_100g' => 12,
            'fats_per_100g' => 4,
        ])->assertRedirect();

        $this->assertEqualsWithDelta(12, (float) $food->fresh()->carbs_per_100g, 0.01);
        $this->assertEqualsWithDelta(21.6, (float) $item->fresh()->carbs, 0.01);
        $this->assertEqualsWithDelta(21.6, (float) $meal->fresh()->load('items.food')->total_carbs, 0.01);
    }
}
