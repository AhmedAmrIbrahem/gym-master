<?php

namespace App\Services\Nutrition;

use App\Models\DailyMeal;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class NutritionHistoryService
{
    /** @return array{paginatedDates: LengthAwarePaginator, daysLoggedThisMonth: int, avgDailyCalories: int|float, avgDailyProtein: int|float} */
    public function forUser(User $user, ?string $date, int $page, string $path, array $queryParameters = []): array
    {
        $query = DailyMeal::where('user_id', $user->id)
            ->whereHas('items')
            ->with('items.food');

        if ($date !== null && $date !== '') {
            $query->whereDate('date', $date);
        }

        $allMeals = $query->orderBy('date', 'desc')->get()
            ->groupBy(fn ($meal) => $meal->date->format('Y-m-d'));

        $paginatedDates = new LengthAwarePaginator(
            $allMeals->slice(($page - 1) * 7, 7),
            $allMeals->count(),
            7,
            $page,
            ['path' => $path, 'query' => $queryParameters]
        );

        $currentMonthMeals = DailyMeal::where('user_id', $user->id)
            ->whereHas('items')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->with('items.food')
            ->get()
            ->groupBy(fn ($meal) => $meal->date->format('Y-m-d'));

        $daysLoggedThisMonth = $currentMonthMeals->count();
        $monthlyTotalCalories = 0;
        $monthlyTotalProtein = 0;

        foreach ($currentMonthMeals as $dayMeals) {
            foreach ($dayMeals as $meal) {
                $monthlyTotalCalories += $meal->total_calories;
                $monthlyTotalProtein += $meal->total_protein;
            }
        }

        return [
            'paginatedDates' => $paginatedDates,
            'daysLoggedThisMonth' => $daysLoggedThisMonth,
            'avgDailyCalories' => $daysLoggedThisMonth > 0 ? round($monthlyTotalCalories / $daysLoggedThisMonth) : 0,
            'avgDailyProtein' => $daysLoggedThisMonth > 0 ? round($monthlyTotalProtein / $daysLoggedThisMonth, 1) : 0,
        ];
    }
}
