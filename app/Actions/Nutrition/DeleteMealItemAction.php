<?php

namespace App\Actions\Nutrition;

use App\Models\MealItem;
use App\Models\User;

class DeleteMealItemAction
{
    public function execute(User $user, MealItem $mealItem): void
    {
        abort_unless((int) $mealItem->dailyMeal->user_id === (int) $user->id, 403);

        $mealItem->delete();
    }
}
