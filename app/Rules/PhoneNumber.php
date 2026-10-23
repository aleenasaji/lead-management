<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('/^\+?[0-9][0-9\s().-]*[0-9]$/D', $value) !== 1) {
            $fail('The :attribute must contain 7 to 15 digits and may include a leading plus sign, spaces, parentheses, dots, or hyphens.');

            return;
        }

        $digitCount = preg_match_all('/[0-9]/', $value);

        if ($digitCount < 7 || $digitCount > 15) {
            $fail('The :attribute must contain 7 to 15 digits and may include a leading plus sign, spaces, parentheses, dots, or hyphens.');
        }
    }
}
