<?php

namespace App\Actions\Nutrition;

use App\Models\DailyMeal;
use App\Models\Food;
use App\Models\MealItem;
use App\Models\User;

class AddMealItemAction
{
    public function execute(User $user, array $data): MealItem
    {
        abort_unless(DailyMeal::query()->whereKey($data['daily_meal_id'])
            ->where('user_id', $user->id)->exists(), 403);

        abort_unless(Food::query()->whereKey($data['food_id'])
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', $user->id))
            ->exists(), 403);

        return MealItem::create($data);
    }
}
