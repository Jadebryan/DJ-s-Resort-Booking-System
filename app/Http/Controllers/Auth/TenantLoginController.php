<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TenantDomain;
use App\Models\TenantModel\Tenant as TenantUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TenantLoginController extends Controller
{
    public function create(): View
    {
        $host = request()->getHost();
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?: $host;
        $onTenantDomain = strtolower($host) !== strtolower((string) $appHost);

        return view('auth.tenantAuth.login', ['onTenantDomain' => $onTenantDomain]);
    }

    public function store(Request $request): RedirectResponse
    {
        $host = $request->getHost();
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?: $host;
        $onTenantDomain = strtolower($host) !== strtolower((string) $appHost);

        if (! $onTenantDomain) {
            $request->validate([
                'tenant_domain' => ['required', 'string', 'max:255'],
            ]);
            $domain = strtolower(trim($request->string('tenant_domain')->toString()));
            $domain = (string) preg_replace('#^https?://#i', '', $domain);
            $domain = rtrim($domain, '/');

            $domain = tenant_primary_domain_storage($domain);

            if (! TenantDomain::where('domain', $domain)->exists()) {
                return back()->withErrors(['tenant_domain' => __('No resort is registered for this web address.')])->onlyInput('tenant_domain');
            }

            return redirect()->away(absolute_url_for_tenant_host($domain, '/login'));
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $tenant = TenantDomain::forRequestHost($host)?->tenant;
        if (! $tenant) {
            abort(404);
        }

        config(['database.connections.tenant.database' => $tenant->database_name]);
        DB::purge('tenant');

        if (! Schema::connection('tenant')->hasTable('tenant_users')) {
            try {
                Artisan::call('migrate', [
                    '--path' => 'database/migrations/tenant',
                    '--database' => 'tenant',
                    '--force' => true,
                ]);
            } catch (\Exception $e) {
                return back()->withErrors([
                    'email' => 'Tenant database not ready: '.$e->getMessage(),
                ])->onlyInput('email');
            }
        }

        $userRecord = DB::connection('tenant')
            ->table('tenant_users')
            ->where('email', $request->email)
            ->first();

        if (! $userRecord || ! Hash::check($request->password, $userRecord->password)) {
            return back()->withErrors([
                'email' => __('The provided credentials are invalid.'),
            ])->onlyInput('email');
        }

        $user = new TenantUser();
        $user->setConnection('tenant');
        $user->forceFill((array) $userRecord);

        Auth::guard('tenant')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $request->session()->put('tenant_database', $tenant->database_name);
        $request->session()->put('tenant_slug', $tenant->slug);
        $request->session()->put('tenant_domain', $host);

        return redirect('/dashboard');
    }
}
