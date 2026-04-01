<?php

namespace App\Http\Controllers\Auth\tenantAuthController;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user('tenant')->hasVerifiedEmail()
                    ? redirect()->intended(route('tenant.dashboard', absolute: false))
                    : view('auth.tenantAuth.verify-email');
    }
}
