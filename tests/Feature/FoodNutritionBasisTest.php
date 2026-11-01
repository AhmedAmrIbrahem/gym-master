<?php

namespace Tests\Feature;

use App\Actions\Nutrition\CreateFoodAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodNutritionBasisTest extends TestCase
{
    use RefreshDatabase;

    public function test_portion_nutrition_is_converted_to_per_100_grams(): void
    {
        $user = User::factory()->create();
        $food = app(CreateFoodAction::class)->execute($user, [
            'name' => 'جبنة قريش',
            'nutrition_reference_grams' => 180,
            'calories_per_100g' => 306,
            'protein_per_100g' => 36,
            'carbs_per_100g' => 0,
            'fats_per_100g' => 12.6,
        ]);

        $this->assertEqualsWithDelta(170, (float) $food->calories_per_100g, 0.001);
        $this->assertEqualsWithDelta(20, (float) $food->protein_per_100g, 0.001);
        $this->assertEqualsWithDelta(7, (float) $food->fats_per_100g, 0.001);
        $this->assertSame($user->id, $food->user_id);
    }

    public function test_existing_per_100_gram_input_keeps_its_values(): void
    {
        $user = User::factory()->create();
        $food = app(CreateFoodAction::class)->execute($user, [
            'name' => 'أرز',
            'calories_per_100g' => 130,
            'protein_per_100g' => 2.7,
            'carbs_per_100g' => 28,
            'fats_per_100g' => 0.3,
        ]);

        $this->assertEqualsWithDelta(130, (float) $food->calories_per_100g, 0.001);
    }
}
