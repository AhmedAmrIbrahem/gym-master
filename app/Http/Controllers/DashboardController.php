<?php

namespace App\Http\Controllers;

use App\Models\BodyMeasurement;
use App\Models\DailyMeal; // استدعاء الموديل الصحيح
use App\Models\WorkoutProgram;
use App\Models\WorkoutSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $creatineTakenToday = DB::table('daily_creatine_intakes')
            ->where('user_id', $user->id)->where('date', $today)->exists();

        // Water is stored per user and calendar date; targets are explicitly chosen by the member.
        $waterGoalMl = DB::table('water_tracking_settings')
            ->where('user_id', $user->id)->value('goal_ml');
        $waterConsumedMl = (int) (DB::table('daily_water_intakes')
            ->where('user_id', $user->id)->where('date', $today)->value('consumed_ml') ?? 0);

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
        $todayCarbs = 0;
        $todayFats = 0;
        foreach ($todayMeals as $m) {
            $todayCalories += $m->total_calories;
            $todayProtein += $m->total_protein;
            $todayCarbs += $m->total_carbs;
            $todayFats += $m->total_fats;
        }

        // 3. أحدث وزن مسجل
        $latestMeasurement = BodyMeasurement::where('user_id', $user->id)->orderBy('date', 'desc')->first();

        // 4. بيانات الرسم البياني للوزن (آخر 7 تسجيلات)
        $weightLogs = BodyMeasurement::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(7)
            ->get()
            ->reverse()
            ->values();

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

        // Real training activity and most recent sessions for the dashboard.
        $recentSessions = WorkoutSession::where('user_id', $user->id)
            ->with('workoutDay')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $trainedDates = WorkoutSession::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $today])
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->flip();

        $weeklyTrainingDays = collect(range(6, 0))
            ->map(function ($offset) use ($trainedDates) {
                $day = now()->subDays($offset);

                return [
                    'date' => $day->toDateString(),
                    'label' => $day->locale('ar')->translatedFormat('D'),
                    'trained' => $trainedDates->has($day->toDateString()),
                ];
            });
        $weeklyTrainingCount = $weeklyTrainingDays->where('trained', true)->count();

        // Display only goals actually configured for this account, without placeholder percentages.
        $nutritionGoals = [];
        foreach ([
            ['target_calories', 'السعرات', $todayCalories, 'كالوري', 'bg-blue-500', 0],
            ['target_protein', 'البروتين', $todayProtein, 'جم', 'bg-emerald-500', 0],
            ['target_carbs', 'الكاربوهيدرات', $todayCarbs, 'جم', 'bg-sky-500', 0],
            ['target_fats', 'الدهون', $todayFats, 'جم', 'bg-amber-500', 0],
        ] as [$field, $label, $actual, $unit, $color, $precision]) {
            $target = (float) ($user->{$field} ?? 0);
            if ($target <= 0) {
                continue;
            }
            $nutritionGoals[] = compact('label', 'actual', 'target', 'unit', 'color', 'precision') + [
                'percentage' => min(100, max(0, round($actual / $target * 100))),
            ];
        }
        $weeklyCaloriesLogged = $caloriesByDate->isNotEmpty();
        $hasWeightData = $weightLogs->isNotEmpty();

        return view('dashboard', compact(
            'user',
            'creatineTakenToday',
            'waterGoalMl',
            'waterConsumedMl',
            'activeProgram',
            'todayWorkout',
            'todayCalories',
            'todayProtein',
            'todayCarbs',
            'todayFats',
            'nutritionGoals',
            'weeklyCaloriesLogged',
            'hasWeightData',
            'latestMeasurement',
            'weightChartLabels',
            'weightChartData',
            'nutritionChartLabels',
            'nutritionChartData',
            'recentSessions',
            'weeklyTrainingDays',
            'weeklyTrainingCount'
        ));
    }

    public function setWaterGoal(Request $request)
    {
        $data = $request->validate(['goal_ml' => ['required', 'integer', 'min:500', 'max:6000']]);
        $userId = auth()->id();
        DB::table('water_tracking_settings')->updateOrInsert(
            ['user_id' => $userId],
            ['goal_ml' => $data['goal_ml'], 'updated_at' => now()]
        );

        return redirect()->route('dashboard');
    }

    public function addWater(Request $request)
    {
        $data = $request->validate(['amount_ml' => ['required', 'integer', 'in:250,500']]);
        $userId = auth()->id();
        $date = now()->toDateString();
        DB::transaction(function () use ($userId, $date, $data) {
            DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            $query = DB::table('daily_water_intakes')->where('user_id', $userId)->where('date', $date);
            $current = (int) ($query->value('consumed_ml') ?? 0);
            $next = min(20000, $current + (int) $data['amount_ml']);
            DB::table('daily_water_intakes')->updateOrInsert(
                ['user_id' => $userId, 'date' => $date],
                ['consumed_ml' => $next, 'updated_at' => now()]
            );
        });

        return redirect()->route('dashboard');
    }

    public function toggleCreatine()
    {
        $userId = auth()->id();
        $date = now()->toDateString();
        DB::transaction(function () use ($userId, $date) {
            DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            $query = DB::table('daily_creatine_intakes')
                ->where('user_id', $userId)->where('date', $date);
            if ($query->exists()) {
                $query->delete();
            } else {
                DB::table('daily_creatine_intakes')->insert([
                    'user_id' => $userId, 'date' => $date, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('dashboard');
    }
}
