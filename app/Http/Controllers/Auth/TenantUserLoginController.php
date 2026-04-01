<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TenantDomain;
use App\Models\TenantUserModel\RegularUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TenantUserLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.tenantUserAuth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $tenant = TenantDomain::forRequestHost($request->getHost())?->tenant;
        if (! $tenant) {
            abort(404);
        }

        $userRecord = DB::connection('tenant')
            ->table('regular_users')
            ->where('email', $request->email)
            ->first();

        if (! $userRecord || ! Hash::check($request->password, $userRecord->password)) {
            return back()->withErrors([
                'email' => __('The provided credentials are invalid.'),
            ])->onlyInput('email');
        }

        $user = new RegularUser();
        $user->setConnection('tenant');
        $user->forceFill((array) $userRecord);

        Auth::guard('regular_user')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $request->session()->put('tenant_slug', $tenant->slug);
        $request->session()->put('tenant_database', $tenant->database_name);
        $request->session()->put('tenant_domain', $request->getHost());

        return redirect('/user/dashboard');
    }
}
