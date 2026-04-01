<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController
{
    /**
     * Display the role selector view.
     */
    public function create(): RedirectResponse
    {
        // Role selector removed; redirect to homepage/search bar
        return redirect('/');
    }

    /**
     * Log the user out of the application.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Logout from all guards
        Auth::guard('admin')->logout();
        Auth::guard('tenant')->logout();
        Auth::guard('regular_user')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
