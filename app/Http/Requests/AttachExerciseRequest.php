<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ownership checks remain in the controllers during this refactor.
    }

    public function rules(): array
    {
        return [
            'exercise_id' => ['required', 'exists:exercises,id'],
            'default_sets' => ['required', 'integer', 'min:1'],
            'default_reps' => ['required', 'integer', 'min:1'],
        ];
    }
}
