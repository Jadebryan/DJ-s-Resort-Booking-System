<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\TenantDomain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetTenantDatabase
{
    /**
     * Resolve tenant only via mapped hostnames (custom domains).
     */
    public function handle(Request $request, Closure $next)
    {
        $appHost = strtolower((string) (parse_url(config('app.url'), PHP_URL_HOST) ?: $request->getHost()));
        $currentHost = strtolower($request->getHost());

        if ($currentHost === $appHost) {
            return $next($request);
        }

        $mappedDomain = TenantDomain::forRequestHost($request->getHost());

        if (! $mappedDomain || ! $mappedDomain->tenant) {
            abort(404, 'Unknown host');
        }

        $tenant = $mappedDomain->tenant;
        if (! $tenant->is_active) {
            if ($this->allowsTenantAccessWhileInactive($request)) {
                return $this->continueWithTenantContext($request, $next, $tenant);
            }

            return response()->view('Tenant.inactive', [
                'tenant' => $tenant,
                'host' => $request->getHost(),
            ], 403);
        }

        return $this->continueWithTenantContext($request, $next, $tenant);
    }

    private function continueWithTenantContext(Request $request, Closure $next, Tenant $tenant): Response
    {
        $this->applyTenantConnection($tenant->database_name);

        $request->attributes->set('tenant', $tenant);

        session([
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
            'tenant_database' => $tenant->database_name,
            'tenant_domain' => $request->getHost(),
        ]);

        URL::defaults(['tenant_domain' => $request->getHost()]);

        return $next($request);
    }

    /**
     * When a tenant is suspended (is_active = false), still allow staff to sign in and use the
     * payment portal so they can submit a renewal for superadmin review.
     */
    private function allowsTenantAccessWhileInactive(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        if ($this->isTenantHostAssetPath($path)) {
            return true;
        }

        $exact = ['/login', '/logout', '/forgot-password', '/payment'];
        if (in_array($path, $exact, true)) {
            return true;
        }

        $prefixes = ['/reset-password', '/payment/'];
        foreach ($prefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function isTenantHostAssetPath(string $path): bool
    {
        return str_starts_with($path, '/build/')
            || str_starts_with($path, '/vendor/livewire')
            || str_starts_with($path, '/livewire/')
            || $path === '/favicon.ico';
    }

    private function applyTenantConnection(string $databaseName): void
    {
        config(['database.connections.tenant.database' => $databaseName]);
        config(['database.connections.tenant.host' => env('DB_HOST', '127.0.0.1')]);
        config(['database.connections.tenant.port' => env('DB_PORT', '3306')]);
        config(['database.connections.tenant.username' => env('DB_USERNAME', 'root')]);
        config(['database.connections.tenant.password' => env('DB_PASSWORD', '')]);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
