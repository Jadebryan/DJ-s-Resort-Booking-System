<?php

namespace App\Support;

class InputRules
{
    /**
     * Letters, spaces, apostrophes, dots, hyphens (Unicode letters).
     *
     * @return array<int, string>
     */
    public static function personName(int $max = 255, bool $required = true): array
    {
        $rules = [
            'string',
            'max:' . $max,
            "regex:/^[\\pL][\\pL\\s.'\\-]*[\\pL.]$/u",
        ];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return $rules;
    }

    /**
     * Letters/numbers/spaces and common punctuation used in names/titles.
     *
     * @return array<int, string>
     */
    public static function title(int $max = 255, bool $required = true): array
    {
        $rules = [
            'string',
            'max:' . $max,
            "regex:/^[\\pL\\pN][\\pL\\pN\\s.'\\-()&,\\/]*[\\pL\\pN)]$/u",
        ];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return $rules;
    }

    /**
     * Digits only (useful for counts, months, etc).
     *
     * @return array<int, string>
     */
    public static function digits(int $minDigits, int $maxDigits, bool $required = true): array
    {
        $rules = [
            'string',
            'min:' . $minDigits,
            'max:' . $maxDigits,
            "regex:/^\\d{{$minDigits},{$maxDigits}}$/",
        ];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return $rules;
    }

    /**
     * Phone numbers: digits, spaces, +, -, parentheses.
     *
     * @return array<int, string>
     */
    public static function phone(int $max = 25, bool $required = false): array
    {
        $rules = [
            'string',
            'max:' . $max,
            "regex:/^[0-9+()\\s\\-]{7,{$max}}$/",
        ];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return $rules;
    }

    /**
     * Reference codes: letters, numbers, dashes/underscores.
     *
     * @return array<int, string>
     */
    public static function reference(int $max = 80, bool $required = true): array
    {
        $rules = [
            'string',
            'max:' . $max,
            'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*$/',
        ];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return $rules;
    }

    /**
     * Money: numeric with up to 2 decimals.
     *
     * @return array<int, string>
     */
    public static function money(bool $required = true, float $min = 0.0): array
    {
        $rules = [
            'numeric',
            'min:' . $min,
            'regex:/^\\d+(\\.\\d{1,2})?$/',
        ];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return $rules;
    }

    /**
     * Payment method label: letters/numbers/spaces and /-._.
     *
     * @return array<int, string>
     */
    public static function paymentMethod(int $max = 80, bool $required = true): array
    {
        $rules = [
            'string',
            'max:' . $max,
            "regex:/^[\\pL\\pN][\\pL\\pN\\s\\-._\\/]*[\\pL\\pN]$/u",
        ];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return $rules;
    }
}

