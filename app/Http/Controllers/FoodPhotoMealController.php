<?php

namespace App\Http\Controllers;

use App\Actions\Nutrition\AddMealItemAction;
use App\Models\DailyMeal;
use App\Models\Food;
use App\Models\FoodPhotoDraft;
use App\Services\Nutrition\FoodPhotoItems;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FoodPhotoMealController extends Controller
{
    public function store(Request $request, string $draft, AddMealItemAction $addItem)
    {
        // Scope before validation: a guessed draft ID cannot reveal another member's data.
        FoodPhotoDraft::whereKey($draft)->where('user_id', $request->user()->id)->firstOrFail();
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:2000-01-01', 'before_or_equal:'.now('Africa/Cairo')->toDateString()],
            'meal_type' => ['required', Rule::in(['إفطار', 'غداء', 'عشاء', 'سناك / وجبة خفيفة'])],
            ...FoodPhotoItems::rules(),
        ], [
            'date.*' => 'اختر تاريخًا صحيحًا من 2000 وحتى اليوم بتوقيت القاهرة.',
            'meal_type.*' => 'اختر نوع الوجبة من القائمة.',
            ...FoodPhotoItems::messages(),
        ]);
        $data = ['date' => $data['date'], 'meal_type' => $data['meal_type'], 'items' => FoodPhotoItems::normalize($data['items'])];
        $hash = hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));

        $result = DB::transaction(function () use ($request, $draft, $data, $hash, $addItem) {
            $user = $request->user();
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $record = FoodPhotoDraft::whereKey($draft)->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            if ($record->consumed_at) {
                abort_unless(hash_equals($record->approved_hash ?? '', $hash), 409, 'تم حفظ هذه المراجعة بالفعل بقيم أخرى. حدّث سجل التغذية.');

                return ['saved' => true, 'replayed' => true, 'meal_id' => $record->saved_meal_id];
            }
            abort_if($record->expires_at->isPast(), 410, 'انتهت صلاحية مراجعة الصورة. أرسل الصورة مرة أخرى.');
            $date = CarbonImmutable::parse($data['date']);
            // Range also finds SQLite's midnight DATE serialization, like the existing nutrition service.
            $meal = DailyMeal::where('user_id', $user->id)->where('meal_type', $data['meal_type'])
                ->where('date', '>=', $data['date'])->where('date', '<', $date->addDay()->toDateString())->first();
            $meal ??= DailyMeal::firstOrCreate(['user_id' => $user->id, 'date' => $data['date'].' 00:00:00', 'meal_type' => $data['meal_type']]);
            foreach ($data['items'] as $item) {
                // Convert reviewed portion totals to the app's existing per-100g food model.
                // Never change an existing public/owned food or any earlier meal's values.
                $food = Food::firstOrCreate([
                    'user_id' => $user->id, 'name' => $item['name'],
                    'calories_per_100g' => round($item['calories'] / $item['grams'] * 100, 2),
                    'protein_per_100g' => round($item['protein'] / $item['grams'] * 100, 2),
                    'carbs_per_100g' => round($item['carbs'] / $item['grams'] * 100, 2),
                    'fats_per_100g' => round($item['fats'] / $item['grams'] * 100, 2),
                ]);
                $addItem->execute($user, ['daily_meal_id' => $meal->id, 'food_id' => $food->id, 'grams' => $item['grams']]);
            }
            $record->update(['consumed_at' => now('UTC'), 'saved_meal_id' => $meal->id, 'approved_hash' => $hash]);

            return ['saved' => true, 'replayed' => false, 'meal_id' => $meal->id];
        });

        return response()->json([...$result, 'url' => route('nutrition.index', ['date' => $data['date']])])->header('Cache-Control', 'private, no-store');
    }
}
