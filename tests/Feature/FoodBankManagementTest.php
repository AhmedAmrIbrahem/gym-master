<?php

namespace Tests\Feature;

use App\Models\DailyMeal;
use App\Models\Food;
use App\Models\MealItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodBankManagementTest extends TestCase
{
    use RefreshDatabase;

    private function fields(): array
    {
        return [
            'name' => 'جبنة قريش',
            'nutrition_reference_grams' => 180,
            'calories_per_100g' => 306,
            'protein_per_100g' => 36,
            'carbs_per_100g' => 0,
            'fats_per_100g' => 12.6,
        ];
    }

    public function test_owner_can_update_food_using_reference_weight(): void
    {
        $user = User::factory()->create();
        $food = Food::create(['user_id' => $user->id, 'name' => 'قديم', 'calories_per_100g' => 100, 'protein_per_100g' => 10, 'carbs_per_100g' => 0, 'fats_per_100g' => 2]);

        $this->actingAs($user)->put(route('nutrition.updateFood', $food), $this->fields())->assertRedirect();
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'calories_per_100g' => 170, 'protein_per_100g' => 20]);
    }

    public function test_other_user_cannot_change_or_delete_food(): void
    {
        $owner = User::factory()->create();
        $food = Food::create(['user_id' => $owner->id, 'name' => 'خاص', 'calories_per_100g' => 100, 'protein_per_100g' => 10, 'carbs_per_100g' => 0, 'fats_per_100g' => 2]);
        $other = User::factory()->create();

        $this->actingAs($other)->put(route('nutrition.updateFood', $food), $this->fields())->assertForbidden();
        $this->delete(route('nutrition.destroyFood', $food))->assertForbidden();
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'name' => 'خاص']);
    }

    public function test_public_food_cannot_be_modified_and_used_food_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $public = Food::create(['name' => 'عام', 'calories_per_100g' => 100, 'protein_per_100g' => 10, 'carbs_per_100g' => 0, 'fats_per_100g' => 2]);
        $own = Food::create(['user_id' => $user->id, 'name' => 'خاص', 'calories_per_100g' => 100, 'protein_per_100g' => 10, 'carbs_per_100g' => 0, 'fats_per_100g' => 2]);
        $meal = DailyMeal::create(['user_id' => $user->id, 'date' => today()->toDateString(), 'meal_type' => 'إفطار']);
        MealItem::create(['daily_meal_id' => $meal->id, 'food_id' => $own->id, 'grams' => 100]);

        $this->actingAs($user)->put(route('nutrition.updateFood', $public), $this->fields())->assertForbidden();
        $this->delete(route('nutrition.destroyFood', $public))->assertForbidden();
        $this->delete(route('nutrition.destroyFood', $own))->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('foods', ['id' => $own->id]);
    }
}
