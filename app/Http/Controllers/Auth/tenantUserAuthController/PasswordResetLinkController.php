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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public const FLOW = 'tenant_user';

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        session()->forget('password_reset');

        return view('auth.tenantUserAuth.forgot-password');
    }

    /**
     * Send a 6-digit OTP to the user's email when the account exists.
     */
    public function store(Request $request, PasswordResetOtpService $otpService): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->string('email')->toString();

        $throttleKey = Str::transliterate(Str::lower($email).'|tenant-user-pwd-otp|'.$request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => __('Too many attempts. Try again in :seconds seconds.', ['seconds' => $seconds]),
            ]);
        }
        RateLimiter::hit($throttleKey, 3600);

        $user = RegularUser::where('email', $email)->first();
        if ($user) {
            $code = $otpService->issueOtp(self::FLOW, $email);
            $label = $this->tenantSiteLabel();
            $user->notify(new PasswordResetOtpNotification($code, $label, PasswordResetOtpService::OTP_TTL_MINUTES));
        }

        session(['password_reset' => ['scope' => self::FLOW, 'email' => $email]]);

        return redirect()->route('tenant.user.password.otp')
            ->with('status', __('If that email is registered, we sent a 6-digit code. Enter it below.'));
    }

    protected function tenantSiteLabel(): string
    {
        $central = \App\Models\Tenant::where('database_name', DB::connection('tenant')->getDatabaseName())->first();

        return $central?->appDisplayName() ?? config('app.name', 'Resort');
    }
}
