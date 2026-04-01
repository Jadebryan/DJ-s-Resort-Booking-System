<?php

namespace App\Http\Controllers\Auth\tenantUserAuthController;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user('regular_user')->hasVerifiedEmail()) {
            return redirect()->intended(route('user.dashboard', absolute: false).'?verified=1');
        }

        if ($request->user('regular_user')->markEmailAsVerified()) {
            event(new Verified($request->user('regular_user')));
        }

        return redirect()->intended(route('user.dashboard', absolute: false).'?verified=1');
    }
}
