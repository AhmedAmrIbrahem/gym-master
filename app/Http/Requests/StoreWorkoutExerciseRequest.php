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
            'sets' => 'required|array',
            'sets.*.weight' => 'nullable|numeric|min:0|max:10000',
            'sets.*.reps' => 'nullable|integer|min:1|max:10000',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
