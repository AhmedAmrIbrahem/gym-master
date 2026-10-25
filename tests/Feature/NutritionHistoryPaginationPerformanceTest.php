<?php

namespace Tests\Feature;

use App\Models\DailyMeal;
use App\Models\Food;
use App\Models\MealItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NutritionHistoryPaginationPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_paginates_logged_dates_in_sql_and_keeps_other_users_out(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 20)->startOfDay());
        $user = User::factory()->create();
        $other = User::factory()->create();
        $food = Food::create([
            'user_id' => null,
            'name' => 'Food for history',
            'calories_per_100g' => 200,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 20,
            'fats_per_100g' => 5,
        ]);

        for ($day = 1; $day <= 10; $day++) {
            $date = sprintf('2026-10-%02d', $day);
            $meal = DailyMeal::create(['user_id' => $user->id, 'date' => $date, 'meal_type' => 'إفطار']);
            MealItem::create(['daily_meal_id' => $meal->id, 'food_id' => $food->id, 'grams' => 100]);

            // Two meals on one date should still count as just one logged day.
            if ($day === 1) {
                $dinner = DailyMeal::create(['user_id' => $user->id, 'date' => $date, 'meal_type' => 'عشاء']);
                MealItem::create(['daily_meal_id' => $dinner->id, 'food_id' => $food->id, 'grams' => 100]);
            }
        }

        $otherMeal = DailyMeal::create(['user_id' => $other->id, 'date' => '2026-10-19', 'meal_type' => 'إفطار']);
        MealItem::create(['daily_meal_id' => $otherMeal->id, 'food_id' => $food->id, 'grams' => 100]);

        $this->actingAs($user)->get(route('nutrition.history'))
            ->assertOk()
            ->assertViewHas('daysLoggedThisMonth', 10)
            ->assertViewHas('avgDailyCalories', 220)
            ->assertViewHas('avgDailyProtein', 11.0)
            ->assertViewHas('paginatedDates', function ($dates) {
                return $dates->total() === 10
                    && $dates->count() === 7
                    && $dates->currentPage() === 1
                    && $dates->first()->first()->date->toDateString() === '2026-10-10';
            });

        $this->actingAs($user)->get(route('nutrition.history', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('paginatedDates', function ($dates) {
                return $dates->total() === 10
                    && $dates->count() === 3
                    && $dates->currentPage() === 2
                    && $dates->first()->first()->date->toDateString() === '2026-10-03';
            });

        $this->actingAs($user)->get(route('nutrition.history', ['date' => '2026-10-01']))
            ->assertOk()
            ->assertViewHas('paginatedDates', function ($dates) {
                return $dates->total() === 1 && $dates->first()->count() === 2;
            });
    }

    public function test_last_day_of_month_is_included_when_sqlite_stores_time_in_date_column(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 20)->startOfDay());
        $user = User::factory()->create();
        $food = Food::create([
            'user_id' => null,
            'name' => 'Last day food',
            'calories_per_100g' => 120,
            'protein_per_100g' => 7,
            'carbs_per_100g' => 15,
            'fats_per_100g' => 3,
        ]);
        $meal = DailyMeal::create([
            'user_id' => $user->id,
            'date' => '2026-10-31',
            'meal_type' => 'إفطار',
        ]);
        MealItem::create(['daily_meal_id' => $meal->id, 'food_id' => $food->id, 'grams' => 100]);

        $this->actingAs($user)->get(route('nutrition.history'))
            ->assertOk()
            ->assertViewHas('daysLoggedThisMonth', 1)
            ->assertViewHas('avgDailyCalories', 120)
            ->assertViewHas('avgDailyProtein', 7.0)
            ->assertViewHas('paginatedDates', fn ($dates) =>
                $dates->total() === 1
                && $dates->first()->contains('id', $meal->id)
            );
    }

    public function test_empty_meal_dates_are_not_counted_or_loaded(): void
    {
        $user = User::factory()->create();
        DailyMeal::create(['user_id' => $user->id, 'date' => now()->toDateString(), 'meal_type' => 'إفطار']);

        $this->actingAs($user)->get(route('nutrition.history'))
            ->assertOk()
            ->assertViewHas('daysLoggedThisMonth', 0)
            ->assertViewHas('paginatedDates', fn ($dates) => $dates->total() === 0);
    }
}
