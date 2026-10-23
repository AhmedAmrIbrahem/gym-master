<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordCharacters implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (! preg_match('/[A-Z]/', $value)) {
            $fail('كلمة المرور ناقصها حرف إنجليزي كبير (A-Z).');
        }

        if (! preg_match('/[a-z]/', $value)) {
            $fail('كلمة المرور ناقصها حرف إنجليزي صغير (a-z).');
        }

        if (! preg_match('/[0-9]/', $value)) {
            $fail('كلمة المرور ناقصها رقم (0-9).');
        }
    }
}
