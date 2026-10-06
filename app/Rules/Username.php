<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Lowercase, URL-safe, 3-30 chars, not reserved. Uniqueness is checked separately. */
class Username implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;
        $min = (int) config('kustore.username.min', 3);
        $max = (int) config('kustore.username.max', 30);

        if (strlen($value) < $min || strlen($value) > $max) {
            $fail("Your username must be between {$min} and {$max} characters.");

            return;
        }

        if (! preg_match(config('kustore.username.pattern'), $value)) {
            $fail('Use lowercase letters, numbers, "-" or "_" only, starting and ending with a letter or number.');

            return;
        }

        if (static::isReserved($value)) {
            $fail('This username is reserved. Please choose another one.');
        }
    }

    public static function isReserved(string $value): bool
    {
        return in_array(strtolower($value), config('kustore.reserved_usernames', []), true);
    }
}
