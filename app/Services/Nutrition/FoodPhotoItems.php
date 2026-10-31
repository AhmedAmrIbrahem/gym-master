<?php

namespace App\Services\Nutrition;

use Illuminate\Support\Facades\Validator;

class FoodPhotoItems
{
    public static function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:12'],
            'items.*' => ['required', 'array:name,grams,calories,protein,carbs,fats'],
            'items.*.name' => ['required', 'string', 'max:120'],
            'items.*.grams' => ['required', 'numeric', 'between:1,5000'],
            'items.*.calories' => ['required', 'numeric', 'between:0,50000'],
            'items.*.protein' => ['required', 'numeric', 'between:0,5000'],
            'items.*.carbs' => ['required', 'numeric', 'between:0,5000'],
            'items.*.fats' => ['required', 'numeric', 'between:0,5000'],
        ];
    }

    public static function normalize(array $items): array
    {
        $validator = Validator::make(['items' => $items], self::rules(), self::messages());
        $validator->after(function ($validator) use ($items) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            foreach ($items as $i => $item) {
                $grams = (float) $item['grams'];
                if (trim($item['name']) === '' || (float) $item['calories'] > $grams * 10
                    || (float) $item['protein'] + (float) $item['carbs'] + (float) $item['fats'] > $grams * 1.3) {
                    $validator->errors()->add('items.'.$i.'.grams', 'راجع الوزن والقيم الغذائية؛ القيم المدخلة لا تتناسب مع كمية الصنف.');
                }
            }
        });
        $validated = $validator->validate()['items'];

        return array_values(array_map(function ($item) {
            $row = ['name' => trim($item['name'])];
            foreach (['grams', 'calories', 'protein', 'carbs', 'fats'] as $key) {
                $row[$key] = round((float) $item[$key], 2);
            }

            return $row;
        }, $validated));
    }

    public static function messages(): array
    {
        return [
            'items.required' => 'أضف صنفًا واحدًا على الأقل.',
            'items.max' => 'الحد الأقصى 12 صنفًا في المراجعة الواحدة.',
            'items.*.name.required' => 'اكتب اسم كل صنف.',
            'items.*.name.max' => 'اسم الصنف لا يتجاوز 120 حرفًا.',
            'items.*.grams.between' => 'كمية الصنف من 1 إلى 5000 جرام.',
            'items.*.*.required' => 'أكمل الوزن والقيم الغذائية لكل صنف.',
            'items.*.*.numeric' => 'اكتب أرقامًا صحيحة للوزن والقيم الغذائية.',
            'items.*.*.between' => 'راجع القيم الغذائية؛ استخدم قيمًا غير سالبة ضمن الحدود المسموحة.',
        ];
    }
}
