<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoginIdentity
{
    private const DIGITS = [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    private const SEPARATORS = [' ', "\t", "\r", "\n", "\u{00A0}", "\u{2007}", "\u{202F}", '(', ')', '-', '.'];

    public static function phone(string $value): ?string
    {
        $value = str_replace(self::SEPARATORS, '', strtr(trim($value), self::DIGITS));
        if (str_starts_with($value, '00')) {
            $value = '+'.substr($value, 2);
        }
        if (preg_match('/^01[0125][0-9]{8}$/D', $value)) {
            $value = '+20'.substr($value, 1);
        } elseif (preg_match('/^201[0125][0-9]{8}$/D', $value)) {
            $value = '+'.$value;
        }

        return preg_match('/^\+[1-9][0-9]{7,14}$/D', $value) ? $value : null;
    }

    public static function canonical(string $value): string
    {
        return str_contains($value, '@') ? Str::lower(trim($value)) : (self::phone($value) ?? trim($value));
    }

    public function userId(string $value): ?int
    {
        $value = trim($value);
        if (str_contains($value, '@')) {
            $matches = User::whereRaw('LOWER(email) = ?', [Str::lower($value)])->limit(2)->pluck('id');
        } else {
            $phone = self::phone($value);
            if ($phone === null) {
                return null;
            }
            $variants = [$phone, '00'.substr($phone, 1)];
            if (preg_match('/^\+201[0125][0-9]{8}$/D', $phone)) {
                $variants[] = '0'.substr($phone, 3);
                $variants[] = substr($phone, 1);
            }

            // Legacy numbers were saved as typed, without a normalized column.
            // Only fixed, application-owned replacements enter SQL; user values stay bound.
            // This expression works on both the deployed MySQL and SQLite tests.
            $expression = "COALESCE(phone, '')";
            foreach ([...array_fill_keys(self::SEPARATORS, ''), ...self::DIGITS] as $from => $to) {
                $expression = 'REPLACE('.$expression.', '.DB::connection()->getPdo()->quote($from).', '.DB::connection()->getPdo()->quote($to).')';
            }
            $matches = User::whereIn(DB::raw($expression), $variants)->limit(2)->pluck('id');
        }

        // Never pick a different account by password if legacy equivalent numbers collide.
        return $matches->count() === 1 ? (int) $matches->sole() : null;
    }
}
