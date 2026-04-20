<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminModel\Admin;
use App\Notifications\PasswordResetOtpNotification;
use App\Services\PasswordResetOtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminPasswordResetController extends Controller
{
    public const FLOW = 'admin';

    public function showEmailForm(): View
    {
        session()->forget('password_reset');

        return view('auth.adminAuth.forgot-password');
    }

    public function sendEmail(Request $request, PasswordResetOtpService $otpService): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email:rfc,dns', 'max:254']]);
        $email = $request->string('email')->toString();

        $throttleKey = Str::transliterate(Str::lower($email).'|admin-pwd-otp|'.$request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => __('Too many attempts. Try again in :seconds seconds.', ['seconds' => $seconds]),
            ]);
        }
        RateLimiter::hit($throttleKey, 3600);

        $admin = Admin::where('email', $email)->first();
        if ($admin) {
            $code = $otpService->issueOtp(self::FLOW, $email);
            $admin->notify(new PasswordResetOtpNotification(
                $code,
                config('app.name', 'Admin'),
                PasswordResetOtpService::OTP_TTL_MINUTES
            ));
        }

        session(['password_reset' => ['scope' => self::FLOW, 'email' => $email]]);

        return redirect()->route('admin.password.otp')
            ->with('status', __('If that email is registered, we sent a 6-digit code. Enter it below.'));
    }

    public function showOtpForm(): View|RedirectResponse
    {
        $flow = session('password_reset');
        if (! is_array($flow) || ($flow['scope'] ?? null) !== self::FLOW || empty($flow['email'])) {
            return redirect()->route('admin.password.request');
        }

        return view('auth.adminAuth.verify-password-otp', [
            'email' => $flow['email'],
        ]);
    }

    public function verifyOtp(Request $request, PasswordResetOtpService $otpService): RedirectResponse
    {
        $flow = session('password_reset');
        if (! is_array($flow) || ($flow['scope'] ?? null) !== self::FLOW || empty($flow['email'])) {
            return redirect()->route('admin.password.request');
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

        return redirect()->route('admin.password.reset')
            ->with('status', __('Code verified. Choose a new password.'));
    }

    public function resendOtp(Request $request, PasswordResetOtpService $otpService): RedirectResponse
    {
        $flow = session('password_reset');
        if (! is_array($flow) || ($flow['scope'] ?? null) !== self::FLOW || empty($flow['email'])) {
            return redirect()->route('admin.password.request');
        }

        $email = $flow['email'];
        $resendKey = Str::transliterate(Str::lower($email).'|pwd-otp-resend|'.self::FLOW.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($resendKey, 1)) {
            $seconds = RateLimiter::availableIn($resendKey);

            return back()->withErrors(['otp' => __('Wait :seconds seconds before requesting another code.', ['seconds' => $seconds])]);
        }
        RateLimiter::hit($resendKey, 60);

        $admin = Admin::where('email', $email)->first();
        if ($admin) {
            $code = $otpService->issueOtp(self::FLOW, $email);
            $admin->notify(new PasswordResetOtpNotification(
                $code,
                config('app.name', 'Admin'),
                PasswordResetOtpService::OTP_TTL_MINUTES
            ));
        }

        return back()->with('status', __('If that email is registered, we sent a new code.'));
    }

    public function showResetForm(PasswordResetOtpService $otpService): View|RedirectResponse
    {
        $flow = session('password_reset');
        if (! is_array($flow) || ($flow['scope'] ?? null) !== self::FLOW || empty($flow['email'])) {
            return redirect()->route('admin.password.request');
        }
        if (! $otpService->hasPasswordResetGrant(self::FLOW, $flow['email'])) {
            return redirect()->route('admin.password.otp')
                ->withErrors(['otp' => __('Please verify your code first.')]);
        }

        return view('auth.adminAuth.reset-password', [
            'email' => $flow['email'],
        ]);
    }

    public function resetPassword(Request $request, PasswordResetOtpService $otpService): RedirectResponse
    {
        $flow = session('password_reset');
        if (! is_array($flow) || ($flow['scope'] ?? null) !== self::FLOW || empty($flow['email'])) {
            return redirect()->route('admin.password.request');
        }
        $email = $flow['email'];
        if (! $otpService->hasPasswordResetGrant(self::FLOW, $email)) {
            return redirect()->route('admin.password.otp')
                ->withErrors(['otp' => __('Please verify your code first.')]);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $admin = Admin::where('email', $email)->first();
        if (! $admin) {
            $otpService->consumeGrant(self::FLOW, $email);
            session()->forget('password_reset');

            return redirect()->route('admin.password.request')
                ->withErrors(['email' => __('Account not found.')]);
        }

        $admin->password = $request->input('password');
        $admin->remember_token = Str::random(60);
        $admin->save();

        event(new PasswordReset($admin));

        $otpService->consumeGrant(self::FLOW, $email);
        session()->forget('password_reset');

        return redirect()->route('admin.login')->with('status', __('Your password has been reset.'));
    }
}
