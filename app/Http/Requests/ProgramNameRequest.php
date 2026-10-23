<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramNameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ownership checks remain in the controllers during this refactor.
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
