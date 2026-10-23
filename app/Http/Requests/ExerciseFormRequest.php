<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExerciseFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ownership checks remain in the controllers during this refactor.
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'target_muscle' => ['required', 'string', 'max:255'],
        ];
    }
}
