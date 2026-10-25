<?php

namespace App\Services\Nutrition;

use App\Models\DailyMeal;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class NutritionHistoryService
{
    /** @return array{paginatedDates: LengthAwarePaginator, daysLoggedThisMonth: int, avgDailyCalories: int|float, avgDailyProtein: int|float} */
    public function forUser(User $user, ?string $date, int $page, string $path, array $queryParameters = []): array
    {
        // Paginate DISTINCT logged dates in the database; never load the full history.
        $paginatedDates = DailyMeal::query()
            ->where('user_id', $user->id)
            ->whereHas('items')
            ->when($date !== null && $date !== '', fn ($query) => $query->whereDate('date', $date))
            ->select('date')
            ->groupBy('date')
            ->orderByDesc('date')
            ->paginate(7, ['date'], 'page', $page)
            ->withPath($path)
            ->appends($queryParameters);

        // Match the exact values selected by the database. SQLite can retain
        // "YYYY-MM-DD 00:00:00" from Eloquent's date cast in a DATE column,
        // while MySQL normalizes the same value to "YYYY-MM-DD". Using the
        // cast Carbon date for whereIn() makes all SQLite results disappear.
        $storedDates = $paginatedDates->getCollection()
            ->map(fn (DailyMeal $meal) => $meal->getRawOriginal('date'))
            ->all();

        // Only hydrate the meals and foods for the seven dates visible on this page.
        $dayMeals = empty($storedDates)
            ? collect()
            : DailyMeal::query()
                ->where('user_id', $user->id)
                ->whereIn('date', $storedDates)
                ->whereHas('items')
                ->with('items.food')
                ->orderByDesc('date')
                ->orderBy('id')
                ->get()
                ->groupBy(fn (DailyMeal $meal) => $meal->date->format('Y-m-d'));

        // Retain the keyed paginator contract used by the existing Blade template.
        $paginatedDates->setCollection($dayMeals);

        // An indexed month range + SQL aggregation replaces loading every food item
        // for the entire month. ROUND per item preserves the existing macro logic.
        $monthStart = now()->startOfMonth();

        $monthlyDays = DB::table('daily_meals as meals')
            ->join('meal_items as items', 'items.daily_meal_id', '=', 'meals.id')
            ->join('foods as foods', 'foods.id', '=', 'items.food_id')
            ->where('meals.user_id', $user->id)
            ->where('meals.date', '>=', $monthStart->toDateString())
            ->where('meals.date', '<', $monthStart->copy()->addMonth()->toDateString())
            ->groupBy('meals.date')
            ->selectRaw('meals.date, SUM(ROUND((items.grams / 100.0) * foods.calories_per_100g, 1)) AS calories, SUM(ROUND((items.grams / 100.0) * foods.protein_per_100g, 1)) AS protein')
            ->get();

        $daysLoggedThisMonth = $monthlyDays->count();

        return [
            'paginatedDates' => $paginatedDates,
            'daysLoggedThisMonth' => $daysLoggedThisMonth,
            'avgDailyCalories' => $daysLoggedThisMonth > 0 ? round($monthlyDays->sum('calories') / $daysLoggedThisMonth) : 0,
            'avgDailyProtein' => $daysLoggedThisMonth > 0 ? round($monthlyDays->sum('protein') / $daysLoggedThisMonth, 1) : 0,
        ];
    }
}
