<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantDomain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainController extends Controller
{
    protected function getTenant(Request $request): Tenant
    {
        if ($request->user('tenant')->role !== 'admin') {
            abort(403, 'Only resort owners can manage domains.');
        }
        $tenant = $request->attributes->get('tenant');
        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        return $tenant->loadMissing('domains');
    }

    public function index(Request $request): View|RedirectResponse
    {
        $tenant = $this->getTenant($request);
        $domains = $tenant->domains;
        return view('Tenant.domains.index', compact('tenant', 'domains'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->getTenant($request);
        $domain = tenant_primary_domain_storage((string) $request->string('domain'));
        $request->merge(['domain' => $domain]);
        $request->validate([
            'domain' => ['required', 'string', 'max:255', 'regex:'.TenantDomain::STORED_DOMAIN_REGEX],
        ]);
        if (TenantDomain::where('domain', $domain)->exists()) {
            return redirect()->route('tenant.domains.index')
                ->withErrors(['domain' => 'This domain is already in use.'])->withInput();
        }
        TenantDomain::create([
            'tenant_id' => $tenant->id,
            'domain' => $domain,
            'is_primary' => $tenant->domains()->count() === 0,
        ]);
        return redirect()->route('tenant.domains.index')
            ->with('success', 'Domain added. Point your DNS A record to this server.');
    }

    public function destroy(Request $request, TenantDomain $domain): RedirectResponse
    {
        $tenant = $this->getTenant($request);
        if ($domain->tenant_id !== $tenant->id) {
            abort(403);
        }
        $domain->delete();
        if ($domain->is_primary && $tenant->domains()->exists()) {
            $tenant->domains()->first()->update(['is_primary' => true]);
        }
        return redirect()->route('tenant.domains.index')
            ->with('success', 'Domain removed.');
    }

    public function setPrimary(Request $request, TenantDomain $domain): RedirectResponse
    {
        $tenant = $this->getTenant($request);
        if ($domain->tenant_id !== $tenant->id) {
            abort(403);
        }
        $tenant->domains()->update(['is_primary' => false]);
        $domain->update(['is_primary' => true]);
        return redirect()->route('tenant.domains.index')
            ->with('success', 'Primary domain updated.');
    }
}
