<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetOtpService
{
    public const OTP_TTL_MINUTES = 15;

    public const GRANT_TTL_MINUTES = 15;

    public const MAX_VERIFY_ATTEMPTS = 5;

    public function issueOtp(string $flowKey, string $email): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put($this->otpCacheKey($flowKey, $email), [
            'hash' => Hash::make($code),
            'attempts' => 0,
        ], now()->addMinutes(self::OTP_TTL_MINUTES));

        return $code;
    }

    public function verifyOtp(string $flowKey, string $email, string $code): bool
    {
        $key = $this->otpCacheKey($flowKey, $email);
        $data = Cache::get($key);
        if (! is_array($data) || empty($data['hash'])) {
            return false;
        }

        $attempts = (int) ($data['attempts'] ?? 0);
        if ($attempts >= self::MAX_VERIFY_ATTEMPTS) {
            Cache::forget($key);

            return false;
        }

        if (! Hash::check($code, $data['hash'])) {
            $data['attempts'] = $attempts + 1;
            Cache::put($key, $data, now()->addMinutes(self::OTP_TTL_MINUTES));

            return false;
        }

        Cache::forget($key);
        Cache::put($this->grantCacheKey($flowKey, $email), true, now()->addMinutes(self::GRANT_TTL_MINUTES));

        return true;
    }

    public function hasPasswordResetGrant(string $flowKey, string $email): bool
    {
        return Cache::has($this->grantCacheKey($flowKey, $email));
    }

    public function consumeGrant(string $flowKey, string $email): void
    {
        Cache::forget($this->grantCacheKey($flowKey, $email));
    }

    public function clearFlow(string $flowKey, string $email): void
    {
        Cache::forget($this->otpCacheKey($flowKey, $email));
        Cache::forget($this->grantCacheKey($flowKey, $email));
    }

    protected function otpCacheKey(string $flowKey, string $email): string
    {
        return 'pwd_otp:'.$flowKey.':'.hash('sha256', Str::lower($email));
    }

    protected function grantCacheKey(string $flowKey, string $email): string
    {
        return 'pwd_otp_grant:'.$flowKey.':'.hash('sha256', Str::lower($email));
    }
}
