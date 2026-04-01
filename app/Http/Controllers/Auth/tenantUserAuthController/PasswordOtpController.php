<?php

namespace App\Http\Controllers\Auth\tenantUserAuthController;

use App\Http\Controllers\Controller;
use App\Models\TenantUserModel\RegularUser;
use App\Notifications\PasswordResetOtpNotification;
use App\Services\PasswordResetOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordOtpController extends Controller
{
    public const FLOW = 'tenant_user';

    public function create(Request $request): View|RedirectResponse
    {
        $flow = session('password_reset');
        if (! is_array($flow) || ($flow['scope'] ?? null) !== self::FLOW || empty($flow['email'])) {
            return redirect()->route('tenant.user.password.request');
        }

        return view('auth.tenantUserAuth.verify-password-otp', [
            'email' => $flow['email'],
        ]);
    }

    public function store(Request $request, PasswordResetOtpService $otpService): RedirectResponse
    {
        $flow = session('password_reset');
        if (! is_array($flow) || ($flow['scope'] ?? null) !== self::FLOW || empty($flow['email'])) {
            return redirect()->route('tenant.user.password.request');
        }

        $request->validate([
            'otp' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ], [
            'otp.regex' => __('Enter the 6-digit code from the email.'),
        ]);

        $email = $flow['email'];
        if (! $otpService->verifyOtp(self::FLOW, $email, $request->input('otp'))) {
            return back()->withErrors(['otp' => __('Invalid or expired code. Try again or request a new code.')]);
        }

        return redirect()->route('tenant.user.password.reset')
            ->with('status', __('Code verified. Choose a new password.'));
    }

    public function resend(Request $request, PasswordResetOtpService $otpService): RedirectResponse
    {
        $flow = session('password_reset');
        if (! is_array($flow) || ($flow['scope'] ?? null) !== self::FLOW || empty($flow['email'])) {
            return redirect()->route('tenant.user.password.request');
        }

        $email = $flow['email'];
        $resendKey = Str::transliterate(Str::lower($email).'|pwd-otp-resend|'.self::FLOW.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($resendKey, 1)) {
            $seconds = RateLimiter::availableIn($resendKey);

            return back()->withErrors(['otp' => __('Wait :seconds seconds before requesting another code.', ['seconds' => $seconds])]);
        }
        RateLimiter::hit($resendKey, 60);

        $user = RegularUser::where('email', $email)->first();
        if ($user) {
            $code = $otpService->issueOtp(self::FLOW, $email);
            $central = \App\Models\Tenant::where('database_name', DB::connection('tenant')->getDatabaseName())->first();
            $label = $central?->appDisplayName() ?? config('app.name', 'Resort');
            $user->notify(new PasswordResetOtpNotification($code, $label, PasswordResetOtpService::OTP_TTL_MINUTES));
        }

        return back()->with('status', __('If that email is registered, we sent a new code.'));
    }
}
