<?php

namespace Tests\Feature;

use App\Models\DailyMeal;
use App\Models\Food;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodCatalogPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function makeFood(?User $user, string $name): Food
    {
        return Food::create([
            'user_id' => $user?->id,
            'name' => $name,
            'calories_per_100g' => 170,
            'protein_per_100g' => 20,
            'carbs_per_100g' => 5,
            'fats_per_100g' => 8,
        ]);
    }

    public function test_search_only_returns_shared_and_owned_foods_without_loading_entire_catalog(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $shared = $this->makeFood(null, 'جبنة عامة');
        $mine = $this->makeFood($owner, 'جبنة بيتي');
        $this->makeFood($other, 'جبنة خاصة بمستخدم آخر');
        $this->makeFood($owner, 'تفاح');

        $this->getJson(route('nutrition.searchFoods', ['q' => 'جبنة']))->assertUnauthorized();

        $response = $this->actingAs($owner)->getJson(route('nutrition.searchFoods', ['q' => 'جبنة']))
            ->assertOk()->assertJsonCount(2, 'foods');
        $ids = array_column($response->json('foods'), 'id');
        $this->assertEqualsCanonicalizing([$shared->id, $mine->id], $ids);
        $this->assertNotEmpty($response->json('foods.0.label'));
        $this->assertDatabaseCount('foods', 4);

        // Remote lookups are capped at 40 records even if the bank is much larger.
        for ($i = 0; $i < 60; $i++) {
            $this->makeFood($owner, sprintf('طعام رقم %03d', $i));
        }
        $this->getJson(route('nutrition.searchFoods'))->assertOk()->assertJsonCount(40, 'foods');
    }

    public function test_nutrition_page_only_preloads_thirty_foods_and_paginates_owned_foods(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        for ($i = 0; $i < 27; $i++) {
            $this->makeFood($owner, sprintf('صنف %03d', $i));
        }
        for ($i = 0; $i < 25; $i++) {
            $this->makeFood($other, sprintf('خاص بالآخر %03d', $i));
        }
        for ($i = 0; $i < 11; $i++) {
            $this->makeFood(null, sprintf('عام %03d', $i));
        }

        $this->actingAs($owner)->get(route('nutrition.index'))
            ->assertOk()
            ->assertViewHas('foods', fn ($foods) => $foods->count() === 30
                && $foods->every(fn (Food $food) => $food->user_id === null || $food->user_id === $owner->id))
            ->assertViewHas('myFoods', fn ($page) => $page->total() === 27
                && $page->count() === 12 && $page->currentPage() === 1)
            ->assertViewHas('ownFoodsCount', 27);

        $this->get(route('nutrition.index', ['bank_page' => 3]))
            ->assertOk()
            ->assertViewHas('myFoods', fn ($page) => $page->total() === 27
                && $page->count() === 3 && $page->currentPage() === 3);

        $this->get(route('nutrition.index', ['bank_q' => 'صنف 00']))
            ->assertOk()
            ->assertViewHas('myFoods', fn ($page) => $page->total() === 10);
    }

    public function test_adding_a_food_found_by_remote_search_does_not_create_another_food(): void
    {
        $owner = User::factory()->create();
        $food = $this->makeFood($owner, 'جبنة قريش');
        $meal = DailyMeal::create([
            'user_id' => $owner->id,
            'date' => today()->toDateString(),
            'meal_type' => 'إفطار',
        ]);
        $this->actingAs($owner)->getJson(route('nutrition.searchFoods', ['q' => 'قريش']))
            ->assertOk()->assertJsonPath('foods.0.id', $food->id);

        $this->post(route('nutrition.storeMealItem'), [
            'daily_meal_id' => $meal->id, 'food_id' => $food->id, 'grams' => 100,
        ])->assertRedirect();

        $this->assertDatabaseCount('foods', 1);
        $this->assertDatabaseHas('meal_items', [
            'daily_meal_id' => $meal->id, 'food_id' => $food->id, 'grams' => 100,
        ]);
    }
    public function test_opening_nutrition_twice_reuses_the_same_daily_meals(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $date = today()->toDateString();
        $existing = DailyMeal::create([
            'user_id' => $owner->id,
            'date' => $date,
            'meal_type' => 'إفطار',
        ]);
        DailyMeal::create([
            'user_id' => $other->id,
            'date' => $date,
            'meal_type' => 'إفطار',
        ]);

        $this->actingAs($owner)->get(route('nutrition.index'))->assertOk();
        $this->get(route('nutrition.index', ['bank_page' => 2]))->assertOk();

        $this->assertSame(4, DailyMeal::query()->where('user_id', $owner->id)->count());
        $this->assertSame(1, DailyMeal::query()->where('user_id', $other->id)->count());
        $this->assertSame(
            $existing->id,
            DailyMeal::query()->where('user_id', $owner->id)
                ->where('meal_type', 'إفطار')->firstOrFail()->id
        );
    }

}
