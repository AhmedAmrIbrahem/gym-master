<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMealItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'daily_meal_id' => 'required|exists:daily_meals,id',
            'food_id' => 'required|exists:foods,id',
            'grams' => 'required|numeric|min:1',
        ];
    }
}
