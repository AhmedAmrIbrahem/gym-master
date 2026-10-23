<?php

namespace App\Services\Nutrition;

use App\Models\DailyMeal;
use App\Models\User;

class DailyNutritionService
{
    /** @return array{dailyMeals: array, totalCalories: float|int, totalProtein: float|int, totalCarbs: float|int, totalFats: float|int} */
    public function forDate(User $user, string $date): array
    {
        $dailyMeals = [];
        foreach (['إفطار', 'غداء', 'عشاء', 'سناك / وجبة خفيفة'] as $type) {
            $meal = DailyMeal::firstOrCreate([
                'user_id' => $user->id,
                'date' => $date,
                'meal_type' => $type,
            ]);
            $dailyMeals[$type] = $meal->load('items.food');
        }

        $totals = ['totalCalories' => 0, 'totalProtein' => 0, 'totalCarbs' => 0, 'totalFats' => 0];
        foreach ($dailyMeals as $meal) {
            $totals['totalCalories'] += $meal->total_calories;
            $totals['totalProtein'] += $meal->total_protein;
            $totals['totalCarbs'] += $meal->total_carbs;
            $totals['totalFats'] += $meal->total_fats;
        }

        return ['dailyMeals' => $dailyMeals, ...$totals];
    }
}
