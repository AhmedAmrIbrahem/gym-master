<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkoutExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'workout_day_id' => 'required|exists:workout_days,id',
            'exercise_id' => 'required|exists:exercises,id',
            'sets' => 'required|array|min:1',
            'sets.*.weight' => 'nullable|required_with:sets.*.reps|numeric|between:0,999.99|decimal:0,2',
            'sets.*.reps' => 'nullable|required_with:sets.*.weight|integer|between:1,1000',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'sets.*.weight.required_with' => 'أدخل الوزن مع عدد العدات، أو اترك المجموعة فارغة.',
            'sets.*.weight.numeric' => 'الوزن لازم يكون رقمًا.',
            'sets.*.weight.between' => 'الوزن لازم يكون من 0 إلى 999.99 كجم.',
            'sets.*.weight.decimal' => 'الوزن يقبل رقمين فقط بعد العلامة العشرية.',
            'sets.*.reps.required_with' => 'أدخل عدد العدات مع الوزن، أو اترك المجموعة فارغة.',
            'sets.*.reps.integer' => 'عدد العدات لازم يكون رقمًا صحيحًا.',
            'sets.*.reps.between' => 'عدد العدات لازم يكون من 1 إلى 1000.',
        ];
    }
}
