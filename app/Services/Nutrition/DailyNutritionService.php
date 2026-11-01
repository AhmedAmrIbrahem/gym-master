<?php

namespace App\Services\Nutrition;

use App\Models\DailyMeal;
use App\Models\User;
use Carbon\Carbon;

class DailyNutritionService
{
    /** @return array{dailyMeals: array, totalCalories: float|int, totalProtein: float|int, totalCarbs: float|int, totalFats: float|int} */
    public function forDate(User $user, string $date): array
    {
        $types = ['إفطار', 'غداء', 'عشاء', 'سناك / وجبة خفيفة'];
        $day = Carbon::parse($date)->toDateString();
        $nextDay = Carbon::parse($day)->addDay()->toDateString();

        // The DATE column may be serialized as Y-m-d (MySQL) or as
        // Y-m-d 00:00:00 (SQLite when written through Eloquent's date cast).
        // A half-open range matches both and still uses the date index.
        $existing = DailyMeal::query()
            ->where('user_id', $user->id)
            ->where('date', '>=', $day)
            ->where('date', '<', $nextDay)
            ->whereIn('meal_type', $types)
            ->with('items.food')
            ->get()
            ->keyBy('meal_type');

        $dailyMeals = [];
        foreach ($types as $type) {
            $meal = $existing->get($type);

            if ($meal === null) {
                // Use the same midnight format as the Eloquent date cast,
                // so firstOrCreate can also find an insert from another request.
                $meal = DailyMeal::firstOrCreate([
                    'user_id' => $user->id,
                    'date' => $day.' 00:00:00',
                    'meal_type' => $type,
                ])->load('items.food');
            }

            $dailyMeals[$type] = $meal;
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
