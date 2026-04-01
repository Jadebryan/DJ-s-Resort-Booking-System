<?php

namespace App\Http\Controllers\Auth\tenantUserAuthController;

use App\Http\Controllers\Controller;
use App\Models\TenantUserModel\RegularUser;
use App\Services\PasswordResetOtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public const FLOW = 'tenant_user';

    /**
     * Display the password reset view.
     */
    public function create(Request $request, PasswordResetOtpService $otpService): View|RedirectResponse
    {
        $flow = session('password_reset');
        if (! is_array($flow) || ($flow['scope'] ?? null) !== self::FLOW || empty($flow['email'])) {
            return redirect()->route('tenant.user.password.request');
        }
        if (! $otpService->hasPasswordResetGrant(self::FLOW, $flow['email'])) {
            return redirect()->route('tenant.user.password.otp')
                ->withErrors(['otp' => __('Please verify your code first.')]);
        }

        return view('auth.tenantUserAuth.reset-password', [
            'email' => $flow['email'],
        ]);
    }

    /**
     * Persist the new password after OTP verification.
     */
    public function store(Request $request, PasswordResetOtpService $otpService): RedirectResponse
    {
        $flow = session('password_reset');
        if (! is_array($flow) || ($flow['scope'] ?? null) !== self::FLOW || empty($flow['email'])) {
            return redirect()->route('tenant.user.password.request');
        }
        $email = $flow['email'];
        if (! $otpService->hasPasswordResetGrant(self::FLOW, $email)) {
            return redirect()->route('tenant.user.password.otp')
                ->withErrors(['otp' => __('Please verify your code first.')]);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = RegularUser::where('email', $email)->first();
        if (! $user) {
            $otpService->consumeGrant(self::FLOW, $email);
            session()->forget('password_reset');

            return redirect()->route('tenant.user.password.request')
                ->withErrors(['email' => __('Account not found.')]);
        }

        $user->forceFill([
            'password' => $request->input('password'),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $otpService->consumeGrant(self::FLOW, $email);
        session()->forget('password_reset');

        return redirect()->route('tenant.user.login')->with('status', __('Your password has been reset.'));
    }
}
