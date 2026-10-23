<?php

namespace Tests\Feature;

use App\Models\DailyMeal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_nutrition_chart_uses_one_weekly_meal_query(): void
    {
        $user = User::factory()->create();
        DailyMeal::create([
            'user_id' => $user->id,
            'date' => now()->subDays(2)->toDateString(),
            'meal_type' => 'إفطار',
        ]);

        $weeklyMealQueries = 0;
        DB::listen(function ($query) use (&$weeklyMealQueries) {
            $sql = strtolower($query->sql);
            if (str_contains($sql, 'daily_meals') && str_contains($sql, 'between')) {
                $weeklyMealQueries++;
            }
        });

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->assertSame(1, $weeklyMealQueries);
    }
}
