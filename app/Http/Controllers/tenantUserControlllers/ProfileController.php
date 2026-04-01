<?php

namespace App\Http\Controllers\tenantUserControlllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\tenantUser\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('TenantUser.profile.edit', [
            'user' => Auth::guard('regular_user')->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = Auth::guard('regular_user')->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('tenant.user.profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Send a verification email.
     */
    public function sendVerification(Request $request): RedirectResponse
    {
        $user = Auth::guard('regular_user')->user();

        if ($user->hasVerifiedEmail()) {
            return Redirect::route('tenant.user.profile.edit');
        }

        $user->sendEmailVerificationNotification();

        return Redirect::route('tenant.user.profile.edit')->with('status', 'verification-link-sent');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::guard('regular_user')->user();

        Auth::guard('regular_user')->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
