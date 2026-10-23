<?php

namespace App\Http\Controllers;

use App\Models\BodyMeasurement;
use App\Models\DailyMeal; // استدعاء الموديل الصحيح
use App\Models\WorkoutProgram;
use App\Models\WorkoutSession;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        Carbon::setLocale('ar');
        $user = auth()->user();
        $today = now()->toDateString();

        // 1. بيانات تمرين اليوم والنظام النشط
        $activeProgram = WorkoutProgram::where('user_id', $user->id)->where('is_active', true)->with('days')->first();
        $todayWorkout = WorkoutSession::where('user_id', $user->id)->where('date', $today)->first();

        // 2. تغذية اليوم
        $todayMeals = DailyMeal::where('user_id', $user->id)
            ->where('date', $today)
            ->with('items.food')
            ->get();

        $todayCalories = 0;
        $todayProtein = 0;
        foreach ($todayMeals as $m) {
            $todayCalories += $m->total_calories;
            $todayProtein += $m->total_protein;
        }

        // 3. أحدث وزن مسجل
        $latestMeasurement = BodyMeasurement::where('user_id', $user->id)->orderBy('date', 'desc')->first();

        // 4. بيانات الرسم البياني للوزن (آخر 7 تسجيلات)
        $weightLogs = BodyMeasurement::where('user_id', $user->id)
            ->orderBy('date', 'asc')
            ->take(7)
            ->get();

        $weightChartLabels = $weightLogs->map(fn ($m) => Carbon::parse($m->date)->format('d/m'))->toArray();
        $weightChartData = $weightLogs->pluck('weight')->toArray();

        // 5. بيانات الرسم البياني للتغذية (آخر 7 أيام)
        $nutritionChartLabels = [];
        $nutritionChartData = [];

        $startDate = now()->subDays(6)->toDateString();
        $caloriesByDate = DailyMeal::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $today])
            ->with('items.food')
            ->get()
            ->groupBy(fn (DailyMeal $meal) => Carbon::parse($meal->date)->toDateString())
            ->map(fn ($meals) => round($meals->sum('total_calories')));

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $nutritionChartLabels[] = Carbon::parse($date)->locale('ar')->translatedFormat('D d/m');
            $nutritionChartData[] = $caloriesByDate->get($date, 0);
        }

        return view('dashboard', compact(
            'user',
            'activeProgram',
            'todayWorkout',
            'todayCalories',
            'todayProtein',
            'latestMeasurement',
            'weightChartLabels',
            'weightChartData',
            'nutritionChartLabels',
            'nutritionChartData'
        ));
    }
}
