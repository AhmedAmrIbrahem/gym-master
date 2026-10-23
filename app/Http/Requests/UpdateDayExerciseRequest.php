<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDayExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ownership checks remain in the controllers during this refactor.
    }

    public function rules(): array
    {
        return [
            'default_sets' => ['required', 'integer', 'min:1'],
            'default_reps' => ['required', 'integer', 'min:1'],
        ];
    }
}
