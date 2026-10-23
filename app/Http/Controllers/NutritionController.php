<?php

namespace App\Http\Controllers;

use App\Actions\Nutrition\AddMealItemAction;
use App\Actions\Nutrition\CreateFoodAction;
use App\Actions\Nutrition\DeleteMealItemAction;
use App\Http\Requests\StoreFoodRequest;
use App\Http\Requests\StoreMealItemRequest;
use App\Models\Food;
use App\Models\MealItem;
use App\Services\Nutrition\DailyNutritionService;
use App\Services\Nutrition\NutritionHistoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NutritionController extends Controller
{
    public function index(Request $request, DailyNutritionService $dailyNutrition)
    {
        Carbon::setLocale('ar');
        $date = $request->get('date', now()->toDateString());

        // الأطعمة المتاحة (العامة + المضافة بواسطة المستخدم)
        $foods = Food::whereNull('user_id')
            ->orWhere('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        ['dailyMeals' => $dailyMeals, 'totalCalories' => $totalCalories,
            'totalProtein' => $totalProtein, 'totalCarbs' => $totalCarbs,
            'totalFats' => $totalFats] = $dailyNutrition->forDate($request->user(), $date);

        return view('nutrition.index', compact(
            'date', 'foods', 'dailyMeals', 'totalCalories', 'totalProtein', 'totalCarbs', 'totalFats'
        ));
    }

    // إضافة طعام جديد لبنك الأطعمة الخاص بالمستخدم
    public function storeFood(StoreFoodRequest $request, CreateFoodAction $action)
    {
        $action->execute($request->user(), $request->validated());

        return back()->with('success', 'تم إضافة الصنف بنجاح لبنك الأطعمة! 🥗');
    }

    // إضافة صنف لوجبة معينة
    public function storeMealItem(StoreMealItemRequest $request, AddMealItemAction $action)
    {
        $action->execute($request->user(), $request->validated());

        return back()->with('success', 'تم إضافة الطعام للوجبة!');
    }

    // حذف صنف من الوجبة
    public function destroyMealItem(Request $request, MealItem $mealItem, DeleteMealItemAction $action)
    {
        $action->execute($request->user(), $mealItem);

        return back()->with('success', 'تم حذف الصنف من الوجبة.');
    }

    public function history(Request $request, NutritionHistoryService $history)
    {
        Carbon::setLocale('ar');

        $data = $history->forUser(
            $request->user(),
            $request->filled('date') ? $request->date : null,
            max(1, (int) $request->get('page', 1)),
            $request->url(),
            $request->query()
        );

        return view('nutrition.history', $data);
    }
}
