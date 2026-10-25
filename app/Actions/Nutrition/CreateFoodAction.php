<?php

namespace App\Actions\Nutrition;

use App\Models\Food;
use App\Models\User;

class CreateFoodAction
{
    public function execute(User $user, array $data): Food
    {
        $data = $this->normalize($data);

        return Food::create([...$data, 'user_id' => $user->id]);
    }

    public function normalize(array $data): array
    {
        // Food and meal calculations use values per 100 g. Accept nutrition
        // label values for another serving size without changing storage.
        $referenceGrams = (float) ($data['nutrition_reference_grams'] ?? 100);
        unset($data['nutrition_reference_grams']);

        foreach (['calories', 'protein', 'carbs', 'fats'] as $nutrient) {
            $key = $nutrient.'_per_100g';
            $data[$key] = round(((float) $data[$key]) * 100 / $referenceGrams, 4);
        }

        return $data;
    }
}
