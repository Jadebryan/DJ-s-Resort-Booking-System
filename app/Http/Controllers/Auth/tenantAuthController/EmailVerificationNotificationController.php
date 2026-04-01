<?php

namespace App\Http\Controllers\Auth\tenantAuthController;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user('tenant')->hasVerifiedEmail()) {
            return redirect()->intended(route('tenant.dashboard', absolute: false));
        }

        $request->user('tenant')->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
