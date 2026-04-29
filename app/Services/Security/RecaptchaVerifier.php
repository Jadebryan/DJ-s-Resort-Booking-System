<?php

declare(strict_types=1);

namespace App\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

final class RecaptchaVerifier
{
    public function enabled(): bool
    {
        return (bool) config('captcha.recaptcha.enabled', false)
            && (string) config('captcha.recaptcha.secret_key', '') !== '';
    }

    public function verify(Request $request, ?string $token): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        $token = is_string($token) ? trim($token) : '';
        if ($token === '') {
            return false;
        }

        $secret = (string) config('captcha.recaptcha.secret_key', '');

        try {
            $resp = Http::asForm()
                ->timeout(8)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);

            if (! $resp->ok()) {
                return false;
            }

            $json = $resp->json();
            return is_array($json) && (bool) ($json['success'] ?? false);
        } catch (\Throwable) {
            return false;
        }
    }
}

