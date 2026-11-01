<?php

namespace App\Services\Nutrition;

use App\Models\Food;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class FoodCatalogService
{
    /** @return Builder<Food> */
    public function availableTo(User $user): Builder
    {
        return Food::query()->where(function (Builder $query) use ($user) {
            $query->whereNull('user_id')->orWhere('user_id', $user->id);
        });
    }

    /** @return Builder<Food> */
    public function ownedBy(User $user): Builder
    {
        return Food::query()->where('user_id', $user->id);
    }

    /** @return Builder<Food> */
    public function matching(Builder $query, string $term): Builder
    {
        if ($term !== '') {
            // Keep substring matching as in the original picker, while bounding
            // response size and restricting records to the requesting account.
            $query->where('name', 'like', '%'.$term.'%');
        }

        return $query->orderBy('name')->orderBy('id');
    }
}
