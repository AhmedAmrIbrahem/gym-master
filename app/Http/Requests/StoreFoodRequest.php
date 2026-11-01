<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'الصنف ده موجود بالفعل في بنك طعامك؛ اختاره من قائمة الوجبة وحدد الجرامات بدل إضافته مرة تانية.',
        ];
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('foods', 'name')
                    ->where('user_id', $this->user()->id)
                    ->ignore($this->route('food')?->id),
            ],
            'nutrition_reference_grams' => 'nullable|numeric|gt:0|max:100000',
            'calories_per_100g' => 'required|numeric|min:0',
            'protein_per_100g' => 'required|numeric|min:0',
            'carbs_per_100g' => 'required|numeric|min:0',
            'fats_per_100g' => 'required|numeric|min:0',
        ];
    }
}
