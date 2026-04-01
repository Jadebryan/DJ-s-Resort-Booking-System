<?php

namespace App\Http\Controllers\Auth\tenantAuthController;

use App\Http\Controllers\Controller;
use App\Models\TenantModel\Tenant as TenantUser;
use App\Services\PasswordResetOtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public const FLOW = 'tenant_staff';

    public function create(Request $request, PasswordResetOtpService $otpService): View|RedirectResponse
    {
        $flow = session('password_reset');
        if (! is_array($flow) || ($flow['scope'] ?? null) !== self::FLOW || empty($flow['email'])) {
            return redirect()->route('tenant.password.request');
        }
        if (! $otpService->hasPasswordResetGrant(self::FLOW, $flow['email'])) {
            return redirect()->route('tenant.password.otp')
                ->withErrors(['otp' => __('Please verify your code first.')]);
        }

        return view('auth.tenantAuth.reset-password', [
            'email' => $flow['email'],
        ]);
    }

    public function store(Request $request, PasswordResetOtpService $otpService): RedirectResponse
    {
        $flow = session('password_reset');
        if (! is_array($flow) || ($flow['scope'] ?? null) !== self::FLOW || empty($flow['email'])) {
            return redirect()->route('tenant.password.request');
        }
        $email = $flow['email'];
        if (! $otpService->hasPasswordResetGrant(self::FLOW, $email)) {
            return redirect()->route('tenant.password.otp')
                ->withErrors(['otp' => __('Please verify your code first.')]);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = TenantUser::where('email', $email)->first();
        if (! $user) {
            $otpService->consumeGrant(self::FLOW, $email);
            session()->forget('password_reset');

            return redirect()->route('tenant.password.request')
                ->withErrors(['email' => __('Account not found.')]);
        }

        $user->forceFill([
            'password' => $request->input('password'),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $otpService->consumeGrant(self::FLOW, $email);
        session()->forget('password_reset');

        return redirect()->route('tenant.login')->with('status', __('Your password has been reset.'));
    }
}
