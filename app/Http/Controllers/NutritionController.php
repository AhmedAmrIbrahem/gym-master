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
use App\Services\Nutrition\FoodCatalogService;
use App\Services\Nutrition\NutritionHistoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NutritionController extends Controller
{
    public function index(Request $request, DailyNutritionService $dailyNutrition, FoodCatalogService $catalog)
    {
        Carbon::setLocale('ar');
        $date = $request->get('date', now()->toDateString());
        $user = $request->user();

        // Only the initial suggestions are rendered in the four meal forms.
        // The searchable picker loads additional foods on demand.
        $foods = $catalog->matching($catalog->availableTo($user), '')
            ->limit(30)->get();

        // Keep the selected item after a validation redirect, even if its name
        // wasn't among the first 30 suggestions.
        $selectedFood = null;
        if ($request->old('food_id')) {
            $selectedFood = $catalog->availableTo($user)
                ->whereKey($request->old('food_id'))->first();
        }

        $rawBankQuery = $request->query('bank_q', '');
        $bankQuery = is_string($rawBankQuery) ? mb_substr(trim($rawBankQuery), 0, 80) : '';
        $ownFoodsCount = $catalog->ownedBy($user)->count();
        $myFoods = $catalog->matching($catalog->ownedBy($user), $bankQuery)
            ->paginate(12, ['*'], 'bank_page')->withQueryString();

        ['dailyMeals' => $dailyMeals, 'totalCalories' => $totalCalories,
            'totalProtein' => $totalProtein, 'totalCarbs' => $totalCarbs,
            'totalFats' => $totalFats] = $dailyNutrition->forDate($user, $date);

        return view('nutrition.index', compact(
            'date', 'foods', 'selectedFood', 'myFoods', 'ownFoodsCount', 'bankQuery',
            'dailyMeals', 'totalCalories', 'totalProtein', 'totalCarbs', 'totalFats'
        ));
    }

    public function searchFoods(Request $request, FoodCatalogService $catalog)
    {
        $rawTerm = $request->query('q', '');
        $term = is_string($rawTerm) ? mb_substr(trim($rawTerm), 0, 80) : '';
        $foods = $catalog->matching($catalog->availableTo($request->user()), $term)
            ->limit(40)
            ->get(['id', 'name', 'calories_per_100g', 'protein_per_100g']);

        return response()->json([
            'foods' => $foods->map(fn (Food $food) => [
                'id' => $food->id,
                'label' => $food->name.' ('.$food->calories_per_100g.' كالوري / '.$food->protein_per_100g.'ج بروتين لكل 100ج)',
            ])->values(),
        ])->header('Cache-Control', 'private, no-store');
    }

    // إضافة طعام جديد لبنك الأطعمة الخاص بالمستخدم
    public function storeFood(StoreFoodRequest $request, CreateFoodAction $action)
    {
        $action->execute($request->user(), $request->validated());

        return back()->with('success', 'تم إضافة الصنف بنجاح لبنك الأطعمة! 🥗');
    }

    public function updateFood(StoreFoodRequest $request, Food $food, CreateFoodAction $action)
    {
        abort_unless($food->user_id !== null && (int) $food->user_id === (int) $request->user()->id, 403);

        $food->update($action->normalize($request->validated()));

        return back()->with('success', 'تم حفظ بيانات الصنف. القيم الجديدة هتظهر في الوجبات المرتبطة بعد تحديث الصفحة.');
    }

    public function destroyFood(Request $request, Food $food)
    {
        abort_unless($food->user_id !== null && (int) $food->user_id === (int) $request->user()->id, 403);

        if (MealItem::query()->where('food_id', $food->id)->exists()) {
            return back()->with('error', 'لا يمكن حذف صنف مستخدم في وجبات مسجلة. يمكنك تعديل بياناته بدلًا من ذلك.');
        }

        $food->delete();

        return back()->with('success', 'تم حذف الصنف من بنك الطعام.');
    }

    // إضافة صنف لوجبة معينة
    public function storeMealItem(StoreMealItemRequest $request, AddMealItemAction $action)
    {
        $action->execute($request->user(), $request->validated());

        return back()->with('success', 'تم تسجيل الكمية داخل الوجبة فقط، من غير إضافة صنف جديد لبنك الطعام.');
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
