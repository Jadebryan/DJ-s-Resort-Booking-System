<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function admin(Request $request): RedirectResponse
    {
        auth('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('admin.login'));
    }

    private function tenantSiteRoot(string $domain): string
    {
        return rtrim(absolute_url_for_tenant_host($domain, '/'), '/').'/';
    }

    public function tenant(Request $request): RedirectResponse
    {
        $domain = session('tenant_domain');
        $slug = session('tenant_slug');

        if (! $domain && $slug) {
            $tenant = Tenant::where('slug', $slug)->first();
            $domain = $tenant?->primaryDomain()?->domain
                ?: TenantDomain::where('tenant_id', $tenant?->id)->orderByDesc('is_primary')->value('domain');
        }

        auth('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($domain) {
            return redirect()->away($this->tenantSiteRoot($domain));
        }

        return redirect()->route('landing');
    }

    public function user(Request $request): RedirectResponse
    {
        $domain = session('tenant_domain');
        $slug = session('tenant_slug');

        if (! $domain && $slug) {
            $tenant = Tenant::where('slug', $slug)->first();
            $domain = $tenant?->primaryDomain()?->domain
                ?: TenantDomain::where('tenant_id', $tenant?->id)->orderByDesc('is_primary')->value('domain');
        }

        auth('regular_user')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($domain) {
            return redirect()->away($this->tenantSiteRoot($domain));
        }

        return redirect()->route('landing');
    }
}
