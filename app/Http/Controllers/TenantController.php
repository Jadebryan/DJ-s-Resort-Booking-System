<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\Tenant;
use App\Models\TenantRegistrationRequest;
use App\Models\TenantDomain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class TenantController extends Controller
{
    /**
     * Public listing of tenants (resorts) for landing page – no auth required.
     */
    public function publicIndex()
    {
        $tenants = Tenant::with(['plan', 'domains'])
            ->orderBy('tenant_name')
            ->get(['id', 'tenant_name']);

        return view('tenants.public-index', compact('tenants'));
    }

    /**
     * Admin: list all tenants (auth:admin required via route group).
     */
    public function index()
    {
        $tenants = Tenant::with(['plan', 'domains'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.tenants.create', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $domain = $this->normalizePrimaryDomainInput($request);

        $validated = $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'primary_domain' => [
                'required',
                'string',
                'max:255',
                'regex:'.TenantDomain::STORED_DOMAIN_REGEX,
                Rule::unique('tenant_domains', 'domain'),
            ],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'subscription_months' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $subscriptionMonths = max(1, min(12, (int) $validated['subscription_months']));
        $planId = $validated['plan_id'] ?: PlatformSetting::instance()->default_plan_id;

        $centralHost = strtolower((string) (parse_url(config('app.url'), PHP_URL_HOST) ?: ''));
        if ($centralHost !== '' && tenant_browser_hostname($domain) === $centralHost) {
            return back()->withInput()->withErrors(['primary_domain' => __('Use a hostname other than the central app host.')]);
        }

        $dbName = 'tenant_' . Str::random(12);
        $hashedPassword = Hash::make($validated['password']);

        try {
            DB::statement('CREATE DATABASE IF NOT EXISTS `' . $dbName . '`');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['primary_domain' => 'Failed to create tenant database: ' . $e->getMessage()]);
        }

        $internalSlug = $this->generateUniqueInternalSlug($domain);

        $tenant = Tenant::create([
            'tenant_name' => $validated['tenant_name'],
            'app_display_name' => $validated['tenant_name'],
            'slug' => $internalSlug,
            'database_name' => $dbName,
            'plan_id' => $planId,
            'is_active' => true,
            'subscription_ends_at' => $planId
                ? now()->addDays(TenantRegistrationRequest::BILLING_DAYS_PER_MONTH * $subscriptionMonths)
                : null,
            'subscription_months' => $planId ? $subscriptionMonths : null,
            'email' => $validated['email'],
            'password' => $hashedPassword,
        ]);

        $tenant->domains()->create([
            'domain' => $domain,
            'is_primary' => true,
        ]);

        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');
        Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--database' => 'tenant',
            '--force' => true,
        ]);

        DB::connection('tenant')->table('tenant_users')->insert([
            'name' => $validated['tenant_name'] . ' Admin',
            'email' => $validated['email'],
            'password' => $hashedPassword,
            'role' => 'admin',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant created successfully.');
    }

    public function edit(Tenant $tenant)
    {
        $tenant->load('plan', 'domains');
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.tenants.edit', compact('tenant', 'plans'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $domain = $this->normalizePrimaryDomainInput($request);

        $primary = $tenant->domains()->where('is_primary', true)->first();

        $validated = $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'primary_domain' => [
                'required',
                'string',
                'max:255',
                'regex:'.TenantDomain::STORED_DOMAIN_REGEX,
                Rule::unique('tenant_domains', 'domain')->ignore($primary?->id),
            ],
            'email' => ['required', 'email'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'subscription_months' => ['required', 'integer', 'min:1', 'max:12'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $subscriptionMonths = max(1, min(12, (int) $validated['subscription_months']));

        $centralHost = strtolower((string) (parse_url(config('app.url'), PHP_URL_HOST) ?: ''));
        if ($centralHost !== '' && tenant_browser_hostname($domain) === $centralHost) {
            return back()->withInput()->withErrors(['primary_domain' => __('Use a hostname other than the central app host.')]);
        }

        $tenant->tenant_name = $validated['tenant_name'];
        $previousPlanId = $tenant->plan_id;
        $previousMonths = (int) ($tenant->subscription_months ?? 1);
        if (($primary?->domain) !== $domain) {
            $tenant->slug = $this->generateUniqueInternalSlug($domain, $tenant->id);
        }
        $tenant->email = $validated['email'];
        $newPlanId = $validated['plan_id'] ?: null;
        $tenant->plan_id = $newPlanId;

        if ($newPlanId === null) {
            $tenant->subscription_ends_at = null;
            $tenant->subscription_months = null;
        } else {
            $tenant->subscription_months = $subscriptionMonths;
            if ($previousPlanId !== $newPlanId || $previousMonths !== $subscriptionMonths) {
                $tenant->subscription_ends_at = now()->addDays(
                    TenantRegistrationRequest::BILLING_DAYS_PER_MONTH * $subscriptionMonths
                );
            }
        }
        if (! empty($validated['password'])) {
            $tenant->password = Hash::make($validated['password']);
        }
        $tenant->save();

        if ($primary) {
            $primary->update(['domain' => $domain]);
        } else {
            $tenant->domains()->create([
                'domain' => $domain,
                'is_primary' => true,
            ]);
        }

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->domains()->delete();
        $dbName = $tenant->database_name;
        $tenant->delete();

        try {
            DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
        } catch (\Exception $e) {
            // Log but don't fail the request
            report($e);
        }

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant deleted successfully.');
    }

    public function activate(Tenant $tenant): RedirectResponse
    {
        if ($tenant->is_active) {
            return redirect()->route('admin.tenants.index')->with('status', 'Tenant is already active.');
        }

        $tenant->forceFill(['is_active' => true])->save();

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant activated successfully.');
    }

    public function deactivate(Tenant $tenant): RedirectResponse
    {
        if (! $tenant->is_active) {
            return redirect()->route('admin.tenants.index')->with('status', 'Tenant is already deactivated.');
        }

        $tenant->forceFill(['is_active' => false])->save();

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant deactivated successfully.');
    }

    public function storeDomain(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate(['domain' => ['required', 'string', 'max:255']]);
        $domain = tenant_primary_domain_storage($validated['domain']);
        if (TenantDomain::where('domain', $domain)->exists()) {
            return redirect()->route('admin.tenants.edit', $tenant)->withErrors(['domain' => 'This domain is already in use.'])->withInput();
        }
        TenantDomain::create([
            'tenant_id' => $tenant->id,
            'domain' => $domain,
            'is_primary' => $tenant->domains()->count() === 0,
        ]);
        return redirect()->route('admin.tenants.edit', $tenant)->with('success', 'Domain added.');
    }

    public function destroyDomain(Request $request, Tenant $tenant, TenantDomain $domain): RedirectResponse
    {
        if ($domain->tenant_id !== $tenant->id) {
            abort(403);
        }
        $domain->delete();
        if ($domain->is_primary && $tenant->domains()->exists()) {
            $tenant->domains()->first()->update(['is_primary' => true]);
        }
        return redirect()->route('admin.tenants.edit', $tenant)->with('success', 'Domain removed.');
    }

    public function setPrimaryDomain(Request $request, Tenant $tenant, TenantDomain $domain): RedirectResponse
    {
        if ($domain->tenant_id !== $tenant->id) {
            abort(403);
        }
        $tenant->domains()->update(['is_primary' => false]);
        $domain->update(['is_primary' => true]);
        return redirect()->route('admin.tenants.edit', $tenant)->with('success', 'Primary domain updated.');
    }

    private function normalizePrimaryDomainInput(Request $request, string $key = 'primary_domain'): string
    {
        $domain = tenant_primary_domain_storage($request->string($key)->toString());
        $request->merge([$key => $domain]);

        return $domain;
    }

    /**
     * Internal DB identifier only — not shown in URLs.
     */
    private function generateUniqueInternalSlug(string $hostname, ?int $ignoreTenantId = null): string
    {
        $base = Str::slug(explode('.', $hostname)[0] ?: 'resort');
        $internalSlug = $base.'-'.Str::lower(Str::random(5));
        while (Tenant::where('slug', $internalSlug)
            ->when($ignoreTenantId, fn ($q) => $q->where('id', '!=', $ignoreTenantId))
            ->exists()) {
            $internalSlug = $base.'-'.Str::lower(Str::random(5));
        }

        return $internalSlug;
    }
}
