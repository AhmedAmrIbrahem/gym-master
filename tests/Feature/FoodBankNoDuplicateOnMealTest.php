<?php

namespace Tests\Feature;

use App\Models\DailyMeal;
use App\Models\Food;
use App\Models\MealItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodBankNoDuplicateOnMealTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_different_portions_of_an_existing_food_does_not_create_food_records(): void
    {
        $user = User::factory()->create();
        $food = Food::create([
            'user_id' => $user->id,
            'name' => 'جبنة قريش',
            'calories_per_100g' => 170,
            'protein_per_100g' => 20,
            'carbs_per_100g' => 3,
            'fats_per_100g' => 7,
        ]);
        $meal = DailyMeal::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'meal_type' => 'إفطار',
        ]);

        foreach ([100, 170] as $grams) {
            $this->actingAs($user)->post(route('nutrition.storeMealItem'), [
                'daily_meal_id' => $meal->id,
                'food_id' => $food->id,
                'grams' => $grams,
            ])->assertRedirect()->assertSessionHas('success');
        }

        $this->assertSame(1, Food::where('user_id', $user->id)->count());
        $this->assertSame(2, MealItem::where('daily_meal_id', $meal->id)->count());
        $this->assertSame([100.0, 170.0], MealItem::where('daily_meal_id', $meal->id)->orderBy('id')->pluck('grams')->map(fn ($value) => (float) $value)->all());
        $this->assertSame([$food->id], MealItem::where('daily_meal_id', $meal->id)->distinct()->pluck('food_id')->all());
    }

    public function test_user_cannot_add_same_food_name_twice_to_food_bank(): void
    {
        $user = User::factory()->create();
        Food::create([
            'user_id' => $user->id,
            'name' => 'جبنة قريش',
            'calories_per_100g' => 170,
            'protein_per_100g' => 20,
            'carbs_per_100g' => 3,
            'fats_per_100g' => 7,
        ]);

        $this->actingAs($user)->post(route('nutrition.storeFood'), [
            'name' => 'جبنة قريش',
            'nutrition_reference_grams' => 100,
            'calories_per_100g' => 170,
            'protein_per_100g' => 20,
            'carbs_per_100g' => 3,
            'fats_per_100g' => 7,
        ])->assertSessionHasErrors('name');

        $this->assertSame(1, Food::where('user_id', $user->id)->count());
    }
}
