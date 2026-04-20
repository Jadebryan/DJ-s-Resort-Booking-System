<?php

namespace App\Support;

use Illuminate\View\ComponentAttributeBag;

/**
 * Client-side hints aligned with {@see InputRules} (maxlength, inputmode, pattern).
 */
class InputHtmlAttributes
{
    public static function personName(int $max = 255): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'maxlength' => (string) $max,
            'autocomplete' => 'name',
            'inputmode' => 'text',
        ]);
    }

    public static function title(int $max = 255): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'maxlength' => (string) $max,
            'inputmode' => 'text',
        ]);
    }

    public static function email(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'maxlength' => '254',
            'autocomplete' => 'email',
            'inputmode' => 'email',
        ]);
    }

    public static function phone(int $max = 25): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'maxlength' => (string) $max,
            'inputmode' => 'tel',
            'autocomplete' => 'tel',
            'pattern' => '[0-9+()\\s\\-]{7,'.$max.'}',
            'title' => __('Use 7–:max digits or common phone symbols (+, spaces, dashes, parentheses).', ['max' => $max]),
        ]);
    }

    public static function reference(int $max = 80): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'maxlength' => (string) $max,
            'pattern' => '[A-Za-z0-9][A-Za-z0-9_-]*',
            'title' => __('Letters, numbers, hyphens, and underscores only.'),
        ]);
    }

    /**
     * Alphanumeric start; allows spaces and common punctuation (matches upgrade / registration refs).
     */
    public static function paymentReference(int $max = 255): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'maxlength' => (string) $max,
            'pattern' => '[A-Za-z0-9][A-Za-z0-9\\s._\\-]*',
            'title' => __('Start with a letter or number; you may use spaces, dots, dashes, and underscores.'),
        ]);
    }

    public static function paymentMethod(int $max = 80): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'maxlength' => (string) $max,
            'inputmode' => 'text',
            'title' => __('Letters, numbers, spaces, and / - . _'),
        ]);
    }

    /**
     * Stored tenant hostname / slug (see {@see \App\Models\TenantDomain::STORED_DOMAIN_REGEX}).
     */
    public static function primaryDomain(): ComponentAttributeBag
    {
        $pattern = '(?:([a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)|([a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z0-9.-]+)';

        return new ComponentAttributeBag([
            'maxlength' => '255',
            'pattern' => $pattern,
            'title' => __('Use a valid hostname: letters, numbers, hyphens, and optional dots.'),
        ]);
    }

    public static function digitsOtp(int $len = 6): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'maxlength' => (string) $len,
            'inputmode' => 'numeric',
            'pattern' => '[0-9]{'.$len.'}',
            'autocomplete' => 'one-time-code',
        ]);
    }

    public static function money(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'inputmode' => 'decimal',
            'min' => '0',
            'step' => '0.01',
        ]);
    }

    public static function textarea(int $max): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'maxlength' => (string) $max,
        ]);
    }
}
