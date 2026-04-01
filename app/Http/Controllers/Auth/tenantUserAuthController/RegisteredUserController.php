<?php

namespace App\Http\Controllers\Auth\tenantUserAuthController;

use App\Http\Controllers\Controller;
use App\Models\TenantDomain;
use App\Models\TenantUserModel\RegularUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.tenantUserAuth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = TenantDomain::forRequestHost($request->getHost())?->tenant;
        if (! $tenant) {
            abort(404);
        }

        $existingUser = DB::connection('tenant')
            ->table('regular_users')
            ->where('email', $request->email)
            ->first();

        if ($existingUser) {
            return back()->withErrors(['email' => 'The email has already been taken.'])->onlyInput('email');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::connection('tenant')->table('regular_users')->insert($userData);

        $user = DB::connection('tenant')
            ->table('regular_users')
            ->where('email', $request->email)
            ->first();

        $userModel = new RegularUser();
        $userModel->setConnection('tenant');
        $userModel->forceFill((array) $user);

        event(new Registered($userModel));

        Auth::guard('regular_user')->login($userModel);

        $request->session()->put('tenant_slug', $tenant->slug);
        $request->session()->put('tenant_database', $tenant->database_name);
        $request->session()->put('tenant_domain', $request->getHost());
        $request->session()->regenerate();

        return redirect('/user/dashboard');
    }
}
