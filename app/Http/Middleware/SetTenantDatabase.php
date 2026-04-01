<?php

namespace App\Http\Middleware;

use App\Models\TenantDomain;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

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
            return response()->view('Tenant.inactive', [
                'tenant' => $tenant,
                'host' => $request->getHost(),
            ], 403);
        }

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
