<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\Security\RecaptchaVerifier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;

final class RecaptchaValid implements ValidationRule
{
    public function __construct(
        private readonly Request $request
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $ok = app(RecaptchaVerifier::class)->verify($this->request, is_string($value) ? $value : null);

        if (! $ok) {
            $fail(__('Captcha verification failed. Please try again.'));
        }
    }
}

