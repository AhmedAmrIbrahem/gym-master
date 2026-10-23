<?php

namespace App\Actions\Nutrition;

use App\Models\Food;
use App\Models\User;

class CreateFoodAction
{
    public function execute(User $user, array $data): Food
    {
        return Food::create([...$data, 'user_id' => $user->id]);
    }
}
